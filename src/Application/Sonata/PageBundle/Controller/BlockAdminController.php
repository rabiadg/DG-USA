<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Application\Sonata\PageBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\BadRequestParamHttpException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends CRUDController<PageBlockInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockAdminController extends CRUDController
{
    public static function getSubscribedServices(): array
    {
        return [
                'sonata.page.block_interactor' => BlockInteractorInterface::class,
                'sonata.block.manager' => BlockServiceManagerInterface::class,
            ] + parent::getSubscribedServices();
    }

    /**
     * @throws AccessDeniedException
     */
    public function savePositionAction(Request $request): Response
    {
        $this->admin->checkAccess('savePosition');

        try {
            // TODO: Change to $request->query->all('filter') when support for Symfony < 5.1 is dropped.
            /** @var array<array{id?: int|string, position?: string, parent_id?: int|string, page_id?: int|string}> $params */
            $params = $request->request->all()['disposition'] ?? [];

            if ([] === $params) {
                throw new HttpException(400, 'wrong parameters');
            }

            $blockInteractor = $this->container->get('sonata.page.block_interactor');
            \assert($blockInteractor instanceof BlockInteractorInterface);

            $result = $blockInteractor->saveBlocksPosition($params);
            $status = 200;
        } catch (HttpException $e) {
            $status = $e->getStatusCode();
            $result = [
                'exception' => \get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $status = 500;
            $result = [
                'exception' => \get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }

        $result = (true === $result) ? 'ok' : $result;

        return $this->renderJson(['result' => $result], $status);
    }

    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        $parameters = $this->admin->getPersistentParameters();

        $blockManager = $this->container->get('sonata.block.manager');
        \assert($blockManager instanceof BlockServiceManagerInterface);

        if (null === $parameters['type']) {
            return $this->renderWithExtraParams('@SonataPage/BlockAdmin/select_type.html.twig', [
                'services' => $blockManager->getServicesByContext('sonata_page_bundle'),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'action' => 'create',
            ]);
        }

        //return parent::createAction($request);

        $this->assertObjectExists($request);

        $this->admin->checkAccess('create');

        // the key used to lookup the template
        $templateKey = 'edit';

        $class = new \ReflectionClass($this->admin->hasActiveSubClass() ? $this->admin->getActiveSubClass() : $this->admin->getClass());

        if ($class->isAbstract()) {
            return $this->renderWithExtraParams(
                '@SonataAdmin/CRUD/select_subclass.html.twig',
                [
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ],
                null
            );
        }

        $newObject = $this->admin->getNewInstance();

        $preResponse = $this->preCreate($request, $newObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($newObject);

        $form = $this->admin->getForm();
        $redirecturl = '';
        $form->setData($newObject);
        $form->handleRequest($request);
        $formView = $form->createView();
        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {
                /** @phpstan-var T $submittedObject */
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);
                $this->admin->checkAccess('create', $submittedObject);

                try {
                    $newObject = $this->admin->create($submittedObject);


                    if ($this->isXmlHttpRequest($request)) {
                        return $this->handleXmlHttpRequestSuccessResponse($request, $newObject, $redirecturl);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_create_success',
                            ['%name%' => $this->escapeHtml($this->admin->toString($newObject))],
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($newObject);
                } catch (ModelManagerException $e) {
                    // NEXT_MAJOR: Remove this catch.
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (ModelManagerThrowable $e) {
                    $this->handleModelManagerThrowable($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if ($this->isXmlHttpRequest($request) && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    $formView = $form->createView();
                    $this->setFormTheme($formView, $this->admin->getFormTheme());
                    $template = $this->admin->getTemplateRegistry()->getTemplate($templateKey);
                    return $this->renderWithExtraParams($template, [
                        'action' => 'create',
                        'form' => $formView,
                        'object' => $newObject,
                        'objectId' => null,
                    ]);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_create_error',
                        ['%name%' => $this->escapeHtml($this->admin->toString($newObject))],
                        'SonataAdminBundle'
                    )
                );
            } elseif ($this->isPreviewRequested($request)) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }


        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $this->admin->getTemplateRegistry()->getTemplate($templateKey);
        // $template = $this->templateRegistry->getTemplate($templateKey);

        return $this->renderWithExtraParams($template, [
            'action' => 'create',
            'form' => $formView,
            'object' => $newObject,
            'objectId' => null,
        ]);
    }

    public function switchParentAction(Request $request): Response
    {
        $blockId = $request->get('block_id');

        if (null === $blockId) {
            throw new BadRequestParamHttpException('block_id', ['int', 'string'], $blockId);
        }

        $parentId = $request->get('parent_id');

        if (null === $parentId) {
            throw new BadRequestParamHttpException('parent_id', ['int', 'string'], $parentId);
        }

        $block = $this->admin->getObject($blockId);

        if (null === $block) {
            throw new BadRequestHttpException(sprintf('Unable to find block with id: "%s"', $blockId));
        }

        $parent = $this->admin->getObject($parentId);

        if (null === $parent) {
            throw new BadRequestHttpException(sprintf('Unable to find parent block with id: "%s"', $parentId));
        }

        $this->admin->checkAccess('switchParent', $block);

        $block->setParent($parent);
        $this->admin->update($block);

        return $this->renderJson(['result' => 'ok']);
    }

    /**
     * @throws AccessDeniedException
     * @throws BadRequestHttpException
     */
    public function composePreviewAction(Request $request): Response
    {
        $existingObject = $this->assertObjectExists($request, true);
        \assert(null !== $existingObject);

        $this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('composePreview', $existingObject);

        $container = $existingObject->getParent();

        if (null === $container) {
            throw new BadRequestHttpException('No parent found, unable to preview an orphan block');
        }

        $this->admin->setSubject($existingObject);

        $blockManager = $this->container->get('sonata.block.manager');
        \assert($blockManager instanceof BlockServiceManagerInterface);
        $blockServices = $blockManager->getServicesByContext('sonata_page_bundle', false);

        return $this->renderWithExtraParams('@SonataPage/BlockAdmin/compose_preview.html.twig', [
            'container' => $container,
            'child' => $existingObject,
            'blockServices' => $blockServices,
            'blockAdmin' => $this->admin,
        ]);
    }

    /**
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function editAction(Request $request): Response
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $existingObject = $this->assertObjectExists($request, true);
        \assert(null !== $existingObject);

        $this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);
        $objectId = $this->admin->getNormalizedIdentifier($existingObject);
        \assert(null !== $objectId);

        $form = $this->admin->getForm();

        $form->setData($existingObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {
                /** @phpstan-var T $submittedObject */
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);

                try {
                    $existingObject = $this->admin->update($submittedObject);

                    if ($this->isXmlHttpRequest($request)) {
                        return $this->handleXmlHttpRequestSuccessResponse($request, $existingObject);
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_edit_success',
                            ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($request, $existingObject);
                } catch (ModelManagerException $e) {
                    // NEXT_MAJOR: Remove this catch.
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (ModelManagerThrowable $e) {
                    $errorMessage = $this->handleModelManagerThrowable($e);

                    $isFormValid = false;
                } catch (LockException $e) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', [
                        '%name%' => $this->escapeHtml($this->admin->toString($existingObject)),
                        '%link_start%' => sprintf('<a href="%s">', $this->admin->generateObjectUrl('edit', $existingObject)),
                        '%link_end%' => '</a>',
                    ], 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if ($this->isXmlHttpRequest($request) && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    //return $response;
                    $formView = $form->createView();
                    $this->setFormTheme($formView, $this->admin->getFormTheme());
                    $template = $this->admin->getTemplateRegistry()->getTemplate($templateKey);
                    return $this->renderWithExtraParams($template, [
                        'action' => 'edit',
                        'form' => $formView,
                        'object' => $existingObject,
                        'objectId' => $objectId,
                    ]);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $errorMessage ?? $this->trans(
                        'flash_edit_error',
                        ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                        'SonataAdminBundle'
                    )
                );
            } elseif ($this->isPreviewRequested($request)) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        $template = $this->admin->getTemplateRegistry()->getTemplate($templateKey);

        return $this->renderWithExtraParams($template, [
            'action' => 'edit',
            'form' => $formView,
            'object' => $existingObject,
            'objectId' => $objectId,
        ]);
    }
}

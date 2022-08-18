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

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Sonata\PageBundle\Entity\BaseBlock;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Block Admin Controller.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class BlockAdminController extends Controller
{
    /**
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function savePositionAction(?Request $request = null)
    {
        $this->admin->checkAccess('savePosition');

        try {
            $params = $request->get('disposition');

            if (!\is_array($params)) {
                throw new HttpException(400, 'wrong parameters');
            }

            $result = $this->get('sonata.page.block_interactor')->saveBlocksPosition($params, false);

            $status = 200;

            $pageAdmin = $this->get('sonata.page.admin.page');
            $pageAdmin->setRequest($request);
            $pageAdmin->update($pageAdmin->getSubject());
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

    public function createAction(?Request $request = null): Response
    {
        $this->admin->checkAccess('create');

        $sharedBlockAdminClass = $this->container->getParameter('sonata.page.admin.shared_block.class');
        if (!$this->admin->getParent() && \get_class($this->admin) !== $sharedBlockAdminClass) {
            throw new PageNotFoundException('You cannot create a block without a page');
        }

        $parameters = $this->admin->getPersistentParameters();

        if (!$parameters['type']) {
            return $this->render('@SonataPage/BlockAdmin/select_type.html.twig', [
                'services' => $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle'),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'action' => 'create',
            ]);
        }

        //return parent::createAction();

        $request = $this->getRequest();

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
                $redirecturl='';
                $form->setData($newObject);
                $form->handleRequest($request);

                if ($form->isSubmitted()) {
                    $isFormValid = $form->isValid();

                    // persist if the form was valid and if in preview mode the preview was approved
                    if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                        /** @phpstan-var T $submittedObject */
                        $submittedObject = $form->getData();
                        $this->admin->setSubject($submittedObject);
                        $this->admin->checkAccess('create', $submittedObject);

                        try {
                            $em = $this->getDoctrine()->getManager();
                            $page = $submittedObject->getPage();
                            if ($page->getStatus() === 'published') {
                                $cms_crud_controller = $this->container->get('cms.crud_controller');
                                $new_entity=$cms_crud_controller->clonePages($page);
                                $newObject = $this->admin->create($submittedObject);
                                $newObject->setPage($new_entity);
                                if ($newObject->getType() == 'sonata.cms.block.page_banner') {
                                    $name = 'Top Content';
                                } else {
                                    $name = 'Main Content';
                                }
                                $record_lastID = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Block')->findOneBy(array('page' => $new_entity, 'parent' => NULL, 'name' => $name));

                                $newObject->setPage($new_entity);
                                $newObject->setParent($record_lastID);
                                $em->persist($newObject);
                                $em->flush($newObject);
                                $redirecturl= $this->generateUrl('admin_sonata_page_page_compose',['id'=>$new_entity->getId()]);

                            } else {
                                $newObject = $this->admin->create($submittedObject);
                            }


                            if ($this->isXmlHttpRequest()) {
                                return $this->handleXmlHttpRequestSuccessResponse($request, $newObject,$redirecturl);
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
                        if ($this->isXmlHttpRequest() && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                            return $response;
                        }

                        $this->addFlash(
                            'sonata_flash_error',
                            $this->trans(
                                'flash_create_error',
                                ['%name%' => $this->escapeHtml($this->admin->toString($newObject))],
                                'SonataAdminBundle'
                            )
                        );
                    } elseif ($this->isPreviewRequested()) {
                        // pick the preview template if the form was valid and preview was requested
                        $templateKey = 'preview';
                        $this->admin->getShow();
                    }
                }

                $formView = $form->createView();
                // set the theme for the current Admin Form
                $this->setFormTheme($formView, $this->admin->getFormTheme());

                // NEXT_MAJOR: Remove this line and use commented line below it instead
                $template = $this->admin->getTemplate($templateKey);
                // $template = $this->templateRegistry->getTemplate($templateKey);

                return $this->renderWithExtraParams($template, [
                    'action' => 'create',
                    'form' => $formView,
                    'object' => $newObject,
                    'objectId' => null,
                ]);
    }

    /**
     * @return Response
     */
    public function switchParentAction(?Request $request = null)
    {
        $this->admin->checkAccess('switchParent');

        $blockId = $request->get('block_id');
        $parentId = $request->get('parent_id');
        if (null === $blockId || null === $parentId) {
            throw new HttpException(400, 'wrong parameters');
        }

        $block = $this->admin->getObject($blockId);
        if (!$block) {
            throw new PageNotFoundException(sprintf('Unable to find block with id %d', $blockId));
        }

        $parent = $this->admin->getObject($parentId);
        if (!$parent) {
            throw new PageNotFoundException(sprintf('Unable to find parent block with id %d', $parentId));
        }

        $block->setParent($parent);
        $this->admin->update($block);

        return $this->renderJson(['result' => 'ok']);
    }

    /**
     * @throws AccessDeniedException
     * @throws PageNotFoundException
     *
     * @return Response
     */
    public function composePreviewAction(?Request $request = null)
    {
        $this->admin->checkAccess('composePreview');

        $blockId = $request->get('block_id');

        /** @var BaseBlock $block */
        $block = $this->admin->getObject($blockId);
        if (!$block) {
            throw new PageNotFoundException(sprintf('Unable to find block with id %d', $blockId));
        }

        $container = $block->getParent();
        if (!$container) {
            throw new PageNotFoundException('No parent found, unable to preview an orphan block');
        }

        $blockServices = $this->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle', false);

        return $this->render('@SonataPage/BlockAdmin/compose_preview.html.twig', [
            'container' => $container,
            'child' => $block,
            'blockServices' => $blockServices,
        ]);
    }

    /**
     * View history revision of object.
     *
     * @param int|string|null $id
     * @param string|null $revision
     *
     * @throws AccessDeniedException If access is not granted
     * @throws NotFoundHttpException If the object or revision does not exist or the audit reader is not available
     *
     * @return Response
     */
    public function historyViewRevisionAction($id = null, $revision = null): Response // NEXT_MAJOR: Remove the unused $id parameter
    {
        $request = $this->getRequest();
        $this->assertObjectExists($request, true);

        $id = $request->get($this->admin->getIdParameter());
        \assert(null !== $id);
        $object = $this->admin->getObject($id);
        \assert(null !== $object);

        $this->admin->checkAccess('historyViewRevision', $object);

        $manager = $this->get('sonata.admin.audit.manager.do-not-use');

        if (!$manager->hasReader($this->admin->getClass())) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the audit reader for class : %s',
                $this->admin->getClass()
            ));
        }

        $reader = $manager->getReader($this->admin->getClass());

        // retrieve the revisioned object
        $object = $reader->find($this->admin->getClass(), $id, $revision);
        if (!$object) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                $id,
                $revision,
                $this->admin->getClass()
            ));
        }

        $this->admin->setSubject($object);

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $this->admin->getTemplate('show');
        // $template = $this->templateRegistry->getTemplate('show');
        //dump($template);die('call');
        return $this->renderWithExtraParams($template, [
            'action' => 'show',
            'object' => $object,
            'elements' => $this->admin->getShow(),
        ]);
    }

    /**
     * Edit action.
     *
     * @param int|string|null $deprecatedId
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response|RedirectResponse
     */
    public function editAction($deprecatedId = null): Response // NEXT_MAJOR: Remove the unused $id parameter
    {
        if (isset(\func_get_args()[0])) {
            @trigger_error(sprintf(
                'Support for the "id" route param as argument 1 at `%s()` is deprecated since'
                . ' sonata-project/admin-bundle 3.62 and will be removed in 4.0,'
                . ' use `AdminInterface::getIdParameter()` instead.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        // the key used to lookup the template
        $templateKey = 'edit';

        $request = $this->getRequest();
        $this->assertObjectExists($request, true);

        $id = $request->get($this->admin->getIdParameter());
        \assert(null !== $id);
        $existingObject = $this->admin->getObject($id);

        \assert(null !== $existingObject);

        $this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);
        $objectId = $this->admin->getNormalizedIdentifier($existingObject);

        $form = $this->admin->getForm();
        $redirecturl='';
        $form->setData($existingObject);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                /** @phpstan-var T $submittedObject */
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);

                try {
                    $em = $this->getDoctrine()->getManager();
                    $page = $submittedObject->getPage();
                    if ($page->getStatus() === 'published' || $page->getStatus()=='rejected') {
                        $cms_crud_controller = $this->container->get('cms.crud_controller');
                        $new_entity=$cms_crud_controller->clonePages($submittedObject);

                        $redirecturl= $this->generateUrl('admin_sonata_page_page_compose',['id'=>$new_entity->getId()]);

                    } else {
                        $existingObject = $this->admin->update($submittedObject);
                    }
                    //$existingObject = $this->admin->update($submittedObject);

                    if ($this->isXmlHttpRequest()) {
                        return $this->handleXmlHttpRequestSuccessResponse($request, $existingObject,$redirecturl);
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
                    return $this->redirectTo($existingObject);
                } catch (ModelManagerException $e) {
                    // NEXT_MAJOR: Remove this catch.
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (ModelManagerThrowable $e) {
                    $this->handleModelManagerThrowable($e);

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
                if ($this->isXmlHttpRequest() && null !== ($response = $this->handleXmlHttpRequestErrorResponse($request, $form))) {
                    return $response;
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_edit_error',
                        ['%name%' => $this->escapeHtml($this->admin->toString($existingObject))],
                        'SonataAdminBundle'
                    )
                );
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $this->admin->getTemplate($templateKey);
        // $template = $this->templateRegistry->getTemplate($templateKey);

        return $this->renderWithExtraParams($template, [
            'action' => 'edit',
            'url'=>$redirecturl,
            'form' => $formView,
            'object' => $existingObject,
            'objectId' => $objectId,
        ]);
    }



    /**
     * Checks whether $needle is equal to $haystack or part of it.
     *
     * @param object|iterable $haystack
     *
     * @return bool true when $haystack equals $needle or $haystack is iterable and contains $needle
     */
    private function equalsOrContains($haystack, object $needle): bool
    {
        if ($needle === $haystack) {
            return true;
        }

        if (is_iterable($haystack)) {
            foreach ($haystack as $haystackItem) {
                if ($haystackItem === $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
        * @phpstan-param T $object
        */
       protected function handleXmlHttpRequestSuccessResponse(Request $request, object $object,$url=null): JsonResponse
       {
           if (empty(array_intersect(['application/json', '*/*'], $request->getAcceptableContentTypes()))) {
               @trigger_error(sprintf(
                   'None of the passed values ("%s") in the "Accept" header when requesting %s %s is supported since sonata-project/admin-bundle 3.82.'
                   .' It will result in a response with the status code 406 (Not Acceptable) in 4.0. You must add "application/json".',
                   implode('", "', $request->getAcceptableContentTypes()),
                   $request->getMethod(),
                   $request->getUri()
               ), \E_USER_DEPRECATED);
           }

           return $this->renderJson([
               'url'=>$url,
               'result' => 'ok',
               'objectId' => $this->admin->getNormalizedIdentifier($object),
               'objectName' => $this->escapeHtml($this->admin->toString($object)),
           ], Response::HTTP_OK);
       }

    /**
     * Delete action.
     *
     * @param int|string|null $id
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     *
     * @return Response|RedirectResponse
     */
    public function deleteAction($id): Response // NEXT_MAJOR: Remove the unused $id parameter
    {

        $request = $this->getRequest();
        $this->assertObjectExists($request, true);

        $id = $request->get($this->admin->getIdParameter());
        \assert(null !== $id);
        $object = $this->admin->getObject($id);
        \assert(null !== $object);

        $this->checkParentChildAssociation($request, $object);

        $this->admin->checkAccess('delete', $object);

        $preResponse = $this->preDelete($request, $object);
        if (null !== $preResponse) {
            return $preResponse;
        }

        if (Request::METHOD_DELETE === $request->getMethod()) {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');

            $objectName = $this->admin->toString($object);

            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'ok'], Response::HTTP_OK, []);
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_delete_success',
                        ['%name%' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerException $e) {
                // NEXT_MAJOR: Remove this catch.
                $this->handleModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'error'], Response::HTTP_OK, []);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        ['%name%' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerThrowable $e) {
                $this->handleModelManagerThrowable($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(['result' => 'error'], Response::HTTP_OK, []);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        ['%name%' => $this->escapeHtml($objectName)],
                        'SonataAdminBundle'
                    )
                );
            }
            $page = $object->getPage();
            $url = $this->generateUrl('admin_sonata_page_page_compose', array('id' => $page->getId()));

            return $this->redirect($url);
            //return $this->redirectTo($page);
        }

        // NEXT_MAJOR: Remove this line and use commented line below it instead
        $template = $this->admin->getTemplate('delete');
        // $template = $this->templateRegistry->getTemplate('delete');

        return $this->renderWithExtraParams($template, [
            'object' => $object,
            'action' => 'delete',
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ]);
    }

}

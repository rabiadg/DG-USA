<?php

namespace App\Application\Sonata\UserBundle\Controller;


use Sonata\AdminBundle\Controller\CRUDController;

use Traffic\ReportsBundle\Entity\TrashRecords;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Sonata\AdminBundle\Util\AdminObjectAclData;
use Application\Sonata\UserBundle\Entity\User;
use Traffic\ReportsBundle\TrafficReportsBundle;

class UserCRUDController extends CRUDController
{


    /**
     * Execute a batch delete.
     *
     * @param ProxyQueryInterface $query
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException If access is not granted
     */
    public function batchActionDelete(ProxyQueryInterface $query, Request $request = null): Response
    {
        $this->admin->checkAccess('batchDelete');

        $request = $this->resolveRequest($request);

        $modelManager = $this->admin->getModelManager();
        try {
            $modelManager->batchDelete($this->admin->getClass(), $query);
            $this->addFlash('sonata_flash_success', 'Users Deleted successfully');
        } catch (ModelManagerException $e) {
            $this->handleModelManagerException($e);
            $this->addFlash('sonata_flash_error', 'flash_batch_delete_error');
        }

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            array('filter' => $this->admin->getFilterParameters())
        ));
    }

    /**
     * Delete action.
     *
     * @param int|string|null $id
     * @param Request $request
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     */
    public function deleteAction($id, Request $request = null): Response
    {

        $request = $this->resolveRequest($request);
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);
        // $userPresentAsInstructor = $this->userExistAsInstructor($id);
        //dumpEntity($userPresentAsInstructor);die;
        //if ( $object->hasGroup( 'Local Admin' ) ||  $object->hasGroup( 'Instructor' ) || $object->hasRole('ROLE_SUPER_ADMIN') ) {
        if ($object->hasRole('ROLE_SUPER_ADMIN')) {
            //if (   $object->hasRole('ROLE_SUPER_ADMIN') ) {
            return new RedirectResponse($this->admin->generateUrl('list', array(
                'filter' => $this->admin->getFilterParameters(),
                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'User restricted can not be deleted.',
                        array('%name%' => $this->admin->toString($object)),
                        'SonataAdminBundle'
                    )
                )
            )));
        }
        if (!$object) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('delete', $object);

        $preResponse = $this->preDelete($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        if ($this->getRestMethod($request) === 'DELETE') {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete', $request);

            $objectName = $this->admin->toString($object);

            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest($request)) {
                    return $this->renderJson(array('result' => 'ok'), 200, array(), $request);
                }
                # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                # deleting user certificate
                //$this->DeleteAllUserCourseCertificates($id);
                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_delete_success',
                        array('%name%' => $this->escapeHtml($objectName)),
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerException $e) {
                $this->handleModelManagerException($e);

                if ($this->isXmlHttpRequest($request)) {
                    return $this->renderJson(array('result' => 'error'), 200, array(), $request);
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        array('%name%' => $this->escapeHtml($objectName)),
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object, $request);
        }

        return $this->render($this->admin->getTemplate('delete'), array(
            //return $this->render( 'ApplicationSonataUserBundle:admin:delete.html.twig', array(
            'object' => $object,
            'action' => 'delete',
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ), null, $request);
    }


    /**
     * To keep backwards compatibility with older Sonata Admin code.
     *
     * @internal
     */
    private function resolveRequest(Request $request = null)
    {
        if (null === $request) {
            return $this->getRequest();
        }

        return $request;
    }

    /**
     * Batch action.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the HTTP method is not POST
     * @throws \RuntimeException     If the batch action is not defined
     */
    public function batchAction(Request $request = null): Response
    {
        $request = $this->resolveRequest($request);
        $restMethod = $this->getRestMethod($request);

        if ('POST' !== $restMethod) {
            throw $this->createNotFoundException(sprintf('Invalid request type "%s", POST expected', $restMethod));
        }

        // check the csrf token
        $this->validateCsrfToken('sonata.batch', $request);

        $confirmation = $request->get('confirmation', false);

        if ($data = json_decode($request->get('data'), true)) {
            $action = $data['action'];
            $idx = $data['idx'];
            $allElements = $data['all_elements'];
            $request->request->replace(array_merge($request->request->all(), $data));
        } else {
            $request->request->set('idx', $request->get('idx', array()));
            $request->request->set('all_elements', $request->get('all_elements', false));

            $action = $request->get('action');
            $idx = $request->get('idx');
            $allElements = $request->get('all_elements');
            $data = $request->request->all();

            unset($data['_sonata_csrf_token']);
        }
        # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        # Custom work to delete users
        $userdata = array('usersDelete' => array(), 'usersNotDelete' => array());
        $idxCustom = array();
        if (count($idx) > 0) {
            $em = $this->getDoctrine()->getManager();
            $userObject = $em->getRepository('App\Application\Sonata\UserBundle\Entity\User')->findBy(array('id' => $idx));
            foreach ($userObject as $item) {
                if ($item->getId() != 1) {
                    $userdata['usersDelete'][] = $item;
                    $idxCustom[] = $item->getId();
                } else {
                    $userdata['usersNotDelete'][] = $item;
                }
            }
        }


        $batchActions = $this->admin->getBatchActions();
        if (!array_key_exists($action, $batchActions)) {
            throw new \RuntimeException(sprintf('The `%s` batch action is not defined', $action));
        }

        $camelizedAction = BaseFieldDescription::camelize($action);
        $isRelevantAction = sprintf('batchAction%sIsRelevant', ucfirst($camelizedAction));

        if (method_exists($this, $isRelevantAction)) {
            $nonRelevantMessage = call_user_func(array($this, $isRelevantAction), $idx, $allElements, $request);
        } else {
            $nonRelevantMessage = count($idx) != 0 || $allElements; // at least one item is selected
        }

        if (!$nonRelevantMessage) { // default non relevant message (if false of null)
            $nonRelevantMessage = 'flash_batch_empty';
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->buildPager();

        if (true !== $nonRelevantMessage) {
            $this->addFlash('sonata_flash_info', $nonRelevantMessage);

            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    array('filter' => $this->admin->getFilterParameters())
                )
            );
        }

        $askConfirmation = isset($batchActions[$action]['ask_confirmation']) ?
            $batchActions[$action]['ask_confirmation'] :
            true;

        if ($askConfirmation && $confirmation != 'ok') {
            $translationDomain = $batchActions[$action]['translation_domain'] ?: $this->admin->getTranslationDomain();
            $actionLabel = $this->trans($batchActions[$action]['label'], array(), $translationDomain);

            $formView = $datagrid->getForm()->createView();
            return $this->render('ApplicationSonataUserBundle:admin:batch_confirmation.html.twig', array(
                'action' => 'list',
                'action_label' => $actionLabel,
                'datagrid' => $datagrid,
                'form' => $formView,
                'data' => $data,
                'userdata' => $userdata,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
            ), null, $request);
        }
        $idx = $idxCustom;


        // execute the action, batchActionXxxxx
        $finalAction = sprintf('batchAction%s', ucfirst($camelizedAction));
        if (!is_callable(array($this, $finalAction))) {
            throw new \RuntimeException(sprintf('A `%s::%s` method must be callable', get_class($this), $finalAction));
        }

        $query = $datagrid->getQuery();

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        $this->admin->preBatchAction($action, $query, $idx, $allElements);

        if (count($idx) > 0) {
            $this->admin->getModelManager()->addIdentifiersToQuery($this->admin->getClass(), $query, $idx);
        } elseif (!$allElements) {
            $query = null;
        }

        return call_user_func(array($this, $finalAction), $query, $request);
    }

}
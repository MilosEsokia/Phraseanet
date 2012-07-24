<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2012 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Controller\Prod;

use Alchemy\Phrasea\Helper\Record\Basket;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Alchemy\Phrasea\RouteProcessor\Basket as BasketRoute;
use Alchemy\Phrasea\Helper;

/**
 *
 * @license     http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link        www.phraseanet.com
 */
class Basket implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        /**
         * This route is used to create a Basket
         *
         * @params name : title (mandatory)
         * @params desc : description (optionnal)
         * @params lst  : Phraseanet serialized record list (optionnal)
         *
         * @accept JSON / YAML
         *
         */
        $controllers->post('/', $this->call('createBasket'));

        /**
         * This route is used to delete a basket
         *
         * @accept JSON / HTML
         *
         */
        $controllers->post('/{basket_id}/delete/', function(Application $app, Request $request, $basket_id) {
                $em = $app['phraseanet.core']->getEntityManager();

                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                $em->remove($basket);
                $em->flush();

                $data = array(
                    'success' => true
                    , 'message' => _('Basket has been deleted')
                );

                if ($request->getRequestFormat() == 'json') {
                    return $app->json($data);
                } else {
                    return $app->redirect('/');
                }
            })->assert('basket_id', '\d+');

        /**
         * Removes a BasketElement
         */
        $controllers->post(
            '/{basket_id}/delete/{basket_element_id}/'
            , function(Application $app, Request $request, $basket_id, $basket_element_id) {
                /* @var $em \Doctrine\ORM\EntityManager */
                $em = $app['phraseanet.core']->getEntityManager();

                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                foreach ($basket->getElements() as $basket_element) {
                    /* @var $basket_element \Entities\BasketElement */
                    if ($basket_element->getId() == $basket_element_id) {
                        $em->remove($basket_element);
                    }
                }

                $em->flush();

                $data = array(
                    'success' => true
                    , 'message' => _('Record removed from basket')
                );

                if ($request->getRequestFormat() == 'json') {
                    return $app->json($data);
                } else {
                    return $app->redirect('/');
                }
            })->assert('basket_id', '\d+')->assert('basket_element_id', '\d+');

        /**
         * Update name and description of a basket
         *
         * @param name string mandatory
         * @param description string optionnal
         *
         */
        $controllers->post('/{basket_id}/update/', function(Application $app, Request $request, $basket_id) {
                $success = false;

                try {
                    $em = $app['phraseanet.core']->getEntityManager();

                    $basket = $em->getRepository('\Entities\Basket')
                        ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                    $basket->setName($request->get('name', ''));
                    $basket->setDescription($request->get('description'));

                    $em->merge($basket);
                    $em->flush();

                    $success = true;
                    $msg = _('Basket has been updated');
                } catch (\Exception_NotFound $e) {
                    $msg = _('The requested basket does not exist');
                } catch (\Exception_Forbidden $e) {
                    $msg = _('You do not have access to this basket');
                } catch (\Exception $e) {
                    $msg = _('An error occurred');
                }

                $data = array(
                    'success' => $success
                    , 'message' => $msg
                    , 'basket'  => array('id' => $basket_id)
                );

                if ($request->getRequestFormat() == 'json') {
                    return $app->json($data);
                } else {
                    return $app->redirect('/');
                }
            })->assert('basket_id', '\d+');

        /**
         * Get the form to update the Basket attributes (name and description)
         */
        $controllers->get('/{basket_id}/update/', function(Application $app, $basket_id) {
                /* @var $em \Doctrine\ORM\EntityManager */
                $em = $app['phraseanet.core']->getEntityManager();

                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                return new Response(
                        $app['twig']->render(
                            'prod/Baskets/Update.html.twig'
                            , array('basket' => $basket)
                        )
                );
            })->assert('basket_id', '\d+');

        /**
         * Get the Basket reorder form
         */
        $controllers->get(
            '/{basket_id}/reorder/'
            , function(Application $app, $basket_id) {
                /* @var $em \Doctrine\ORM\EntityManager */
                $em = $app['phraseanet.core']->getEntityManager();

                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                return new Response(
                        $app['twig']->render(
                            'prod/Baskets/Reorder.html.twig'
                            , array('basket' => $basket)
                        )
                );
            })->assert('basket_id', '\d+');

        $controllers->post(
            '/{basket_id}/reorder/'
            , function(Application $app, $basket_id) {
                $ret = array('success' => false, 'message' => _('An error occured'));
                try {
                    /* @var $em \Doctrine\ORM\EntityManager */
                    $em = $app['phraseanet.core']->getEntityManager();

                    $basket = $em->getRepository('\Entities\Basket')
                        ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                    $order = $app['request']->get('element');

                    /* @var $basket \Entities\Basket */
                    foreach ($basket->getElements() as $basketElement) {
                        if (isset($order[$basketElement->getId()])) {
                            $basketElement->setOrd($order[$basketElement->getId()]);

                            $em->merge($basketElement);
                        }
                    }

                    $em->flush();
                    $ret = array('success' => true, 'message' => _('Basket updated'));
                } catch (\Exception $e) {

                }

                return $app->json($ret);
            })->assert('basket_id', '\d+');

        /**
         * Toggle the status of a Basket
         *
         * @param acrhive : 0|1 (mandatory)
         *
         * @returns JSON / HTML
         */
        $controllers->post('/{basket_id}/archive/', function(Application $app, Request $request, $basket_id) {
                $em = $app['phraseanet.core']->getEntityManager();

                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                $archive_status = ! ! $request->get('archive');

                $basket->setArchived($archive_status);

                $em->merge($basket);
                $em->flush();

                if ($archive_status) {
                    $message = _('Basket has been archived');
                } else {
                    $message = _('Basket has been unarchived');
                }

                $data = array(
                    'success' => true
                    , 'archive' => $archive_status
                    , 'message' => $message
                );

                if ($request->getRequestFormat() == 'json') {
                    return $app->json($data);
                } else {
                    return $app->redirect('/');
                }
            })->assert('basket_id', '\d+');

        /**
         * Add a BasketElement to a basket
         */
        $controllers->post(
            '/{basket_id}/addElements/'
            , function(Application $app, Request $request, $basket_id) {
                $em = $app['phraseanet.core']->getEntityManager();

                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                $user = $app['phraseanet.core']->getAuthenticatedUser();
                /* @var $user \User_Adapter */

                $n = 0;

                $helper = new Basket($app['phraseanet.core'], $app['request']);

                foreach ($helper->get_elements() as $record) {
                    if ($basket->hasRecord($record))
                        continue;

                    $basket_element = new \Entities\BasketElement();
                    $basket_element->setRecord($record);
                    $basket_element->setBasket($basket);

                    $em->persist($basket_element);

                    $basket->addBasketElement($basket_element);

                    if (null !== $validationSession = $basket->getValidation()) {

                        $participants = $validationSession->getParticipants();

                        foreach ($participants as $participant) {
                            $validationData = new \Entities\ValidationData();
                            $validationData->setParticipant($participant);
                            $validationData->setBasketElement($basket_element);

                            $em->persist($validationData);
                        }
                    }

                    $n ++;
                }

                $em->flush();

                $data = array(
                    'success' => true
                    , 'message' => sprintf(_('%d records added'), $n)
                );

                if ($request->getRequestFormat() == 'json') {
                    return $app->json($data);
                } else {
                    return $app->redirect('/');
                }
            })->assert('basket_id', '\d+');

        /**
         *
         * Move Basket element from a basket to another
         *
         * @params elements Array : list of basket element id
         *
         */
        $controllers->post(
            '/{basket_id}/stealElements/'
            , function(Application $app, Request $request, $basket_id) {
                $em = $app['phraseanet.core']->getEntityManager();

                /* @var $em \Doctrine\ORM\EntityManager */
                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), true);

                $user = $app['phraseanet.core']->getAuthenticatedUser();
                /* @var $user \User_Adapter */

                $n = 0;

                foreach ($request->get('elements') as $bask_element_id) {
                    try {
                        $basket_element = $em->getRepository('\Entities\BasketElement')
                            ->findUserElement($bask_element_id, $user);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $basket_element->setBasket($basket);
                    $basket->addBasketElement($basket_element);
                    $n ++;
                }

                $em->flush();

                $data = array(
                    'success' => true
                    , 'message' => sprintf(_('%d records moved'), $n)
                );

                if ($request->getRequestFormat() == 'json') {
                    return $app->json($data);
                } else {
                    return $app->redirect('/');
                }
            })->assert('basket_id', '\d+');

        /**
         * Get basket creation form
         */
        $controllers->get('/create/', function(Application $app) {

                return new Response($app['twig']->render('prod/Baskets/Create.html.twig', array()));
            });

        /**
         * Get a basket
         */
        $controllers->get('/{basket_id}/', function(Application $app, Request $request, $basket_id) {
                $em = $app['phraseanet.core']->getEntityManager();

                $basket = $em->getRepository('\Entities\Basket')
                    ->findUserBasket($basket_id, $app['phraseanet.core']->getAuthenticatedUser(), false);

                if ($basket->getIsRead() === false) {
                    $basket->setIsRead(true);
                    $em->flush();
                }

                if ($basket->getValidation()) {
                    if ($basket->getValidation()->getParticipant($app['phraseanet.core']->getAuthenticatedUser())->getIsAware() === false) {
                        $basket->getValidation()->getParticipant($app['phraseanet.core']->getAuthenticatedUser())->setIsAware(true);
                        $em->flush();
                    }
                }

                $params = array(
                    'basket' => $basket,
                    'ordre'  => $request->get('order')
                );

                $html = $app['twig']->render('prod/WorkZone/Basket.html.twig', $params);

                return new Response($html);
            })->assert('basket_id', '\d+');

        return $controllers;
    }

    public function createBasket(Application $app)
    {
        $request = $app['request'];
        /* @var $request \Symfony\Component\HttpFoundation\Request */

        $em = $app['phraseanet.core']->getEntityManager();

        $Basket = new \Entities\Basket();

        $Basket->setName($request->get('name', ''));
        $Basket->setOwner($app['phraseanet.core']->getAuthenticatedUser());
        $Basket->setDescription($request->get('desc'));

        $em->persist($Basket);

        $n = 0;

        $helper = new Basket($app['phraseanet.core'], $app['request']);

        foreach ($helper->get_elements() as $record) {

            if ($Basket->hasRecord($record))
                continue;

            $basket_element = new \Entities\BasketElement();
            $basket_element->setRecord($record);
            $basket_element->setBasket($Basket);

            $em->persist($basket_element);

            $Basket->addBasketElement($basket_element);

            $n ++;
        }

        $em->flush();

        if ($request->getRequestFormat() == 'json') {
            $data = array(
                'success' => true
                , 'message' => _('Basket created')
                , 'basket'  => array(
                    'id' => $Basket->getId()
                )
            );

            return $app->json($data);
        } else {
            return $app->redirect(sprintf('/%d/', $Basket->getId()));
        }
    }

    /**
     * Prefix the method to call with the controller class name
     *
     * @param  string $method The method to call
     * @return string
     */
    private function call($method)
    {
        return sprintf('%s::%s', __CLASS__, $method);
    }
}

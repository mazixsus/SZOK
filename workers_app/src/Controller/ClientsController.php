<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 02.12.2018
 * Time: 17:21
 */

namespace App\Controller;


use App\Entity\Rezerwacje;
use App\Entity\Tranzakcje;
use App\Entity\Uzytkownicy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class ClientsController extends AbstractController
{
    /**
     * @Route("/clients/{page<[1-9]\d*>?1}",
     *      name="workers_app/clients",
     *      methods={"GET"})
     */
    public function list($page)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Uzytkownicy::class)->getPageCountOfActive($pageLimit);
            if ($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('worker_app/employees/list');
            else {
                $clientList = $this->getDoctrine()->getRepository(Uzytkownicy::class)->findActive($page, $pageLimit);
                return $this->render('workersApp/clients/list.html.twig', array(
                    'clientList' => $clientList,
                    'currentPage' => $page,
                    'pageCount' => $pageCount,
                    'blocked' => false));
            }
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/clients/blocked/{page<[1-9]\d*>?1}",
     *       name="workers_app/clients/blocked",
     *       methods={"GET"})
     *
     */
    public function blockedList($page)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Uzytkownicy::class)->getPageCountOfDisable($pageLimit);
            if ($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('worker_app/employees/list');

            $clientList = $this->getDoctrine()->getRepository(Uzytkownicy::class)->findDisable($page, $pageLimit);
            return $this->render('workersApp/clients/list.html.twig', array(
                'clientList' => $clientList,
                'currentPage' => $page,
                'pageCount' => $pageCount,
                'blocked' => true));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/clients/show/{id<[1-9]\d*>},{pageTra<[1-9]\d*>?1},{pageRes<[1-9]\d*>?1}", name="workers_app/clients/show")
     */
    public function show($id, $pageTra, $pageRes)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $pageLimit = 5;
            $client = $this->getDoctrine()->getRepository(Uzytkownicy::class)->find($id);
            if (!$client) {
                return $this->redirectToRoute('workers_app/no_permission');
            }
            $reservations = $this->getDoctrine()->getRepository(Rezerwacje::class)->getClientReservationsPage($client, $pageRes, $pageLimit);
            $transactions = $this->getDoctrine()->getRepository(Tranzakcje::class)->getClientTransactionsPage($client, $pageTra, $pageLimit);
            $pageCountRes = $this->getDoctrine()->getRepository(Rezerwacje::class)->getClientReservationsPageCount($client, $pageLimit);
            $pageCountTra = $this->getDoctrine()->getRepository(Tranzakcje::class)->getClientTransactionsPageCount($client, $pageLimit);

            $error = null;
            if ($pageRes > $pageCountRes and $pageCountRes != 0) {
                $pageRes = 1;
                $error = 1;
            }
            if ($pageTra > $pageCountTra and $pageCountTra != 0) {
                $pageTra = 1;
                $error = 1;
            }
            if ($error) {
                return $this->redirectToRoute('workers_app/clients/show', array('id' => $id, 'pgTra' => $pageTra, 'pgRes' => $pageRes));
            } else {
                return $this->render('workersApp/clients/show.html.twig', array(
                    'client' => $client,
                    'transactions' => $transactions,
                    'reservations' => $reservations,
                    'pageCountRes' => $pageCountRes,
                    'pageCountTra' => $pageCountTra,
                    'currentPageTra' => $pageTra,
                    'currentPageRes' => $pageRes
                ));
            }
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workers_app/no_permission');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/clients/delete/{id<[1-9]\d*>}", name="workers_app/clients/delete", methods={"DELETE"})
     */
    public function deleteUser($id)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $user = $this->getDoctrine()->getRepository(Uzytkownicy::class)->find($id);
            if ($user) {
                $reservations = $this->getDoctrine()->getRepository(Rezerwacje::class)->findBy(array('uzytkownicy' => $id));
                $entityManager = $this->getDoctrine()->getManager();
                foreach ($reservations as $key => $reservation) {
                    $reservation->setUzytkownicy(null);
                    $reservation->setCzyodwiedzajacy(1);
                    $entityManager->merge($reservation);
                }
                $transactions = $this->getDoctrine()->getRepository(Tranzakcje::class)->findBy(array('uzytkownicy' => $id));
                foreach ($transactions as $key => $transaction) {
                    $transaction->setUzytkownicy(null);
                    $transaction->setCzyodwiedzajacy(1);
                    $entityManager->merge($transaction);
                }
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }
    }

    /**
     * @Route("/clients/{action?block}/{id<[1-9]\d*>}",
     *      name="workers_app/clients/block",
     *      requirements={"action":"block"},
     *      methods={"DELETE"})
     *
     *
     * @Route("/clients/{action?unblock}/{id<[1-9]\d*>}",
     *      name="workers_app/clients/unblock",
     *      requirements={"action":"unblock"},
     *      methods={"DELETE"})
     */

    public function blockUser($id, $action)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $user = $this->getDoctrine()->getRepository(Uzytkownicy::class)->find($id);
            if ($user) {
                $entityManager = $this->getDoctrine()->getManager();
                if ($action == 'block' && ($user->getCzyzablokowany() == 0 || $user->getCzyzablokowany() == null))
                    $user->setCzyzablokowany(1);
                elseif ($action == 'unblock' && $user->getCzyzablokowany() == 1)
                    $user->setCzyzablokowany(null);

                $entityManager->flush();
            }
        }
    }
}
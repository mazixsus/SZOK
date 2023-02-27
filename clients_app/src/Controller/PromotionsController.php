<?php
/**
 * Created by PhpStorm.
 * User: gnowa
 * Date: 28.10.2018
 * Time: 16:56
 */

namespace App\Controller;

use App\Entity\Promocje;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class PromotionsController extends Controller
{
    /**
     * @Route("/promotions/{page<[1-9]\d*>?1}", name="clients_app/promotions", methods={"GET"})
     */
    public function index($page)
    {
        if ($this->isGranted('ROLE_USER') and AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        $pageLimit = $this->getParameter('page_limit');
        $pageCount = $this->getDoctrine()->getRepository(Promocje::class)->getPageCountOfActual($pageLimit);

        if($page > $pageCount and $pageCount != 0)
            return $this->redirectToRoute('workers_app/promotions');
        else {
            $promotions = $this->getDoctrine()->getRepository(Promocje::class)->findActual($page, $pageLimit);
            return $this->render('clientsApp/promotions/list.html.twig', array('promotions' => $promotions, 'currentPage' => $page, 'pageCount' => $pageCount));
        }
    }

    /**
     * @Route("/promotions/old/{page<[1-9]\d*>?1}", name="clients_app/promotions/old", methods={"GET"})
     */
    public function old($page)
    {
        if ($this->isGranted('ROLE_USER') and AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        $pageLimit = $this->getParameter('page_limit');
        $pageCount = $this->getDoctrine()->getRepository(Promocje::class)->getPageCountOfOld($pageLimit);

        if($page > $pageCount and $pageCount != 0)
            return $this->redirectToRoute('clients_app/promotions/old');
        else {
            $promotions = $this->getDoctrine()->getRepository(Promocje::class)->findOld($page, $pageLimit);
            return $this->render('clientsApp/promotions/listOld.html.twig', array('promotions' => $promotions, 'currentPage' => $page, 'pageCount' => $pageCount));
        }
    }
}
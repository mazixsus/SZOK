<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AppController extends AbstractController
{
    /**
     * @Route("/login", name="workers_app/login_page")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->isGranted('IS_AUTHENTICATED_FULLY')){
            return $this->redirectToRoute('workers_app/main_page');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('workersApp/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="workers_app/logout_page")
     */
    public function logout()
    {
    }

    /**
     * @Route("/", name="workers_app/main_page", methods={"GET"})
     */
    public function index()
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('workersApp/mainPage/mainPage.html.twig');
        else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/no-permission", name="workers_app/no_permission", methods={"GET"})
     */
    public function noPermission()
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('IS_AUTHENTICATED_FULLY'))
            return $this->render('workersApp/mainPage/noPermission.html.twig');
        else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    public static function logoutOnSessionLifetimeEnd(Session $session){
        if ((time() - strtotime($session->get('lifetime'))) > 60*30) {
            return true;
        }else{
            $session->set(
                'lifetime',
                date("Y-m-d H:i:s"));
            return false;
        }
    }
}

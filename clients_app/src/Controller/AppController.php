<?php
/**
 * Created by PhpStorm.
 * User: gnowa
 * Date: 23.10.2018
 * Time: 09:01
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AppController extends Controller
{
    /**
     * @Route("/login", name="clients_app/login_page")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->isGranted('ROLE_USER')){
            return $this->redirectToRoute('clients_app/main_page');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the users
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('clientsApp/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);

    }

    /**
     * @Route("/logout", name="clients_app/logout_page")
     */
    public function logout()
    {
    }

    public static function logoutOnSessionLifetimeEnd(Session $session){
        if ((time() - strtotime($session->get('lifetime'))) > 30*60) {
            return true;
        }else{
            $session->set(
                'lifetime',
                date("Y-m-d H:i:s"));
            $session->start();
            return false;
        }
    }

}
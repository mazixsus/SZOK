<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 17.12.2018
 * Time: 15:33
 */

namespace App\Controller;


use App\Entity\Seanse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProgramController extends AbstractController
{
    /**
     * @Route("/program",
     *      name="clients_app/program")
     */
    public function repertoireDate()
    {
        if ($this->isGranted('ROLE_USER') and AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        $dateMin = new \DateTime(date("Y-m-d"));
        if ($_POST) {
            if ($_POST['date'] <= $dateMin)
                $date = new \DateTime($_POST['date']);
            else
                $date = $dateMin;
        } else {
            $date = $dateMin;
        }

        $seances = $this->getDoctrine()->getRepository(Seanse::class)->getProgram($date);

        return $this->render('clientsApp/program/date.html.twig', array(
            'seances' => $seances,
            'date' => $date,
            'dateMin' => $dateMin,
            ));
    }
}
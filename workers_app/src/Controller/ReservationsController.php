<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 21.11.2018
 * Time: 12:45
 */

namespace App\Controller;


use App\Entity\Miejsca;
use App\Entity\Rezerwacje;
use App\Entity\Seanse;
use App\Entity\Typyrzedow;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReservationsController extends AbstractController
{
    /**
     * @Route("/reservations/{date},{id},{name},{surname}/{page<[1-9]\d*>?1}", name="workers_app/reservations", methods={"GET", "POST"})
     */
    public function index(Request $request, $page, $date, $id, $name, $surname)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {

            if ($values = $request->request->all()) {
                if ($values['id'] == null) {
                    $values['id'] = 0;
                }
                if ($values['date'] == null) {
                    $values['date'] = 0;
                }
                if ($values['name'] == null) {
                    $values['name'] = 0;
                }
                if ($values['surname'] == null) {
                    $values['surname'] = 0;
                }
                return $this->redirectToRoute('workers_app/reservations', $values);
            }

            if ($id === '0') {
                $id = null;
            }
            if ($date === '0') {
                $date = null;
            }
            if ($name === '0') {
                $name = null;
            }
            if ($surname === '0') {
                $surname = null;
            }

            $values = [
                'number' => $id,
                'date' => $date,
                'name' => $name,
                'surname' => $surname
            ];

            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Rezerwacje::class)->getPageCount(
                $id, $date, $name, $surname, $pageLimit);

            if ($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('workers_app/reservations', $values);
            else {
                $reservations = $this->getDoctrine()->getRepository(Rezerwacje::class)->getReservations(
                    $id, $date, $name, $surname, $page, $pageLimit);
                return $this->render('workersApp/reservations/list.html.twig', array('reservations' => $reservations, 'values' => $values, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/reservations/add/{id<[1-9]\d*>?}", name="workers_app/reservations/add", methods={"GET", "POST"})
     */
    public function add(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (!$seance = $this->getDoctrine()->getRepository(Seanse::class)->find($id) or $seance->getCzyodwolany()) {
                return $this->redirectToRoute('workers_app/no_permission');
            }
            $submit = $request->request->get('submitNumber');
            $reservation = new Rezerwacje();
            $form = $this->getForm($reservation);
            $form->handleRequest($request);

            $seatsIdArray = explode(",", $request->get('seatId'));
            $roomLayout = $this->getRoomLayout($this->getDoctrine()->getRepository(Seanse::class)->find($id));
            $error = null;
            if (($submit == 0 || $submit == 2) && $form->isSubmitted() && $form->isValid() && $this->validSeat($seatsIdArray, $roomLayout)) {
                if ($submit == 2) {
                    $seatsArrayCollection = new ArrayCollection();
                    foreach ($seatsIdArray as $seatId) {
                        $seatsArrayCollection->add($this->getDoctrine()->getRepository(Miejsca::class)->find($seatId));
                    }
                    $reservation->setCzyodwiedzajacy(0);
                    $reservation->setSfinalizowana(0);
                    $reservation->setPracownicy($this->getUser());
                    $reservation->setSeanse($seance);
                    $reservation->setMiejsca($seatsArrayCollection);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($reservation);
                    $entityManager->flush();

                    $values = [
                        'id' => $reservation->getId(),
                        'date' => 0,
                        'name' => 0,
                        'surname' => 0
                    ];

                    return $this->redirectToRoute('workers_app/reservations', $values);
                }
                return $this->render('workersApp/reservations/summary.html.twig', ['seance' => $seance, 'rezervationData' => $request->request->all()]);
            } else if ($submit and $submit != 1) {
                $error = 'Coś poszło nie tak';
            }
            $rowType = $this->getDoctrine()->getRepository(Typyrzedow::class)->findAll();
            return $this->render('workersApp/reservations/add.html.twig', ['seance' => $seance,
                'roomLayout' => $roomLayout, 'checkedSeats' => $seatsIdArray, 'form' => $form->createView(),
                'rowType' => $rowType, 'error' => $error]);
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    private function getRoomLayout($seance)
    {

        $room = $seance->getSale();
        $roomLayout = array();
        foreach ($room->getRzedy()->getIterator() AS $row) {
            foreach ($row->getMiejsca()->getIterator() AS $seat) {
                $roomLayout[$seat->getId()] = array(
                    'miejsca' => $seat,
                    'status' => $row->getTypyrzedow()->getId());
            }
        }
        foreach ($seance->getTranzakcje()->getIterator() AS $transaction) {
            foreach ($transaction->getBilety()->getIterator() AS $ticket) {
                if (!$ticket->getCzyanulowany()) {
                    $transSeat = $ticket->getMiejsca();
                    if ($roomLayout[$transSeat->getId()]['status'] == 1
                        or $roomLayout[$transSeat->getId()]['status'] == 2) {
                        $roomLayout[$transSeat->getId()]['status'] = 4;
                    }
                }
            }
        }
        foreach ($seance->getRezerwacje()->getIterator() AS $booking) {
            if (!$booking->isSfinalizowana()) {
                foreach ($booking->getMiejsca()->getIterator() AS $revSeat) {
                    if ($roomLayout[$revSeat->getId()]['status'] == 1
                        or $roomLayout[$revSeat->getId()]['status'] == 2) {
                        $roomLayout[$revSeat->getId()]['status'] = 3;
                    }
                }
            }
        }

        return $roomLayout;
    }

    private function validSeat($seatsIdArray, $roomLayout)
    {
        foreach ($seatsIdArray as $seatId) {
            if (!preg_match('/^[0-9]{1,}$/', $seatId)) {
                return false;
            }

            if (!array_key_exists($seatId, $roomLayout)) {
                return false;
            }

            if ($roomLayout[$seatId]['status'] != 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Rezerwacje $reservation
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getForm(Rezerwacje $reservation)
    {
        return $this->createFormBuilder($reservation)
            ->add('imie', TextType::class, array(
                'label' => 'Imię:',
                'attr' => array('class' => 'form-control',
                    "pattern" => "^[A-ZĄĘÓŁŚŻŹĆŃ][a-zA-ZĄĘÓŁŚŻŹĆŃąęółśżźćń ]{2,44}",
                    'title' => 'Polskie znaki, spacja, pierwsza duża litera, od 3 do 45 znaków',
                    'placeholder' => 'Wprowadź imię...',
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('nazwisko', TextType::class, array(
                'label' => 'Nazwisko:',
                'attr' => array('class' => 'form-control',
                    "pattern" => "[A-ZĄĘÓŁŚŻŹĆŃ][a-zA-ZĄĘÓŁŚŻŹĆŃąęółśżźćń \-]{2,44}",
                    'title' => 'Polskie znaki, spacja, myślnik, pierwsza duża litera, od 3 do 45 znaków',
                    'placeholder' => 'Wprwadź nazwisko...',
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('email', EmailType::class, array(
                'label' => 'E-mail:',
                'attr' => array('class' => 'form-control',
                    "placeholder" => "Wprowdź email...",
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('telefon', TextType::class, array(
                'label' => 'Telefon:',
                'attr' => array("class" => "form-control",
                    "pattern" => "[0-9]{9}",
                    "title" => "9 cyfr",
                    "placeholder" => 'Wprowadź numer telefonu...',
                    'autocomplete' => "off"),
                'label_attr' => array('class' => "col-sm-2 col-form-label")
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Zatwierdz',
                'attr' => array('class' => "btn btn-primary float-right",
                    'onsubmit' => "return onsub()")
            ))
            ->getForm();
    }

    /**
     * @Route("/reservations/delete", name="workers_app/reservations/delete", methods={"DELETE"})
     */
    public function delete(Request $request)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $reservation = $this->getDoctrine()->getRepository(Rezerwacje::class)->find($request->get('reservationId'));
            if(!$reservation or $reservation->isSfinalizowana()){
                return new JsonResponse(['result' => false]);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();
            return new JsonResponse(['result' => true]);
        }
    }
}
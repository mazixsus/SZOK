<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 17.11.2018
 * Time: 17:58
 */

namespace App\Controller;


use App\Entity\Bilety;
use App\Entity\Miejsca;
use App\Entity\Promocje;
use App\Entity\Pulebiletow;
use App\Entity\Rezerwacje;
use App\Entity\Rodzajeplatnosci;
use App\Entity\Seanse;
use App\Entity\Tranzakcje;
use App\Entity\Typyrzedow;
use App\Entity\Vouchery;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    /**
 * @Route("/transaction/add/{id<[1-9]\d*>?}", name="workers_app/transactions/add", methods={"GET", "POST"})
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
            $tickets = $this->getDoctrine()->getRepository(Pulebiletow::class)->getSeancesTickets($id);
            $roomLayout = $this->getRoomLayout($this->getDoctrine()->getRepository(Seanse::class)->find($id));

            $submit = $request->request->get('submitNumber');

            $selectedSeatsIdArray = [];
            $requestArray = $request->request->all();
            $error = null;
            if (!empty($requestArray) && !$submit) {
                $selectedSeatsIdArray = array_keys($requestArray);
                $selectedTicketsIdArray = array_values($requestArray);
                if ($this->validSeat($selectedSeatsIdArray, $roomLayout) && $this->validTickets($selectedTicketsIdArray, $id)) {

                    $selectedTickets = [];
                    $selectedSeats = $this->getDoctrine()->getRepository(Miejsca::class)->findById($selectedSeatsIdArray);
                    $pmrbs = [];
                    foreach ($seance->getPulebiletow()->getPulaMaRodzajeBiletow()->getIterator() as $pmrb) {
                        $pmrbs[$pmrb->getId()] = $pmrb;
                    }
                    for ($i = 0; $i < count($selectedTicketsIdArray); $i++) {
                        $selectedTickets[] = $pmrbs[$selectedTicketsIdArray[$i]];
                    }
                    $promotions = $this->getDoctrine()->getRepository(Promocje::class)->findCurrent();
                    $paymentWay = $this->getDoctrine()->getRepository(Rodzajeplatnosci::class)->findAll();
                    return $this->render('workersApp/transactions/summary.html.twig', ['seance' => $seance,
                        'selectedSeats' => $selectedSeats, 'selectedTickets' => $selectedTickets,
                        'promotions' => $promotions, 'paymentWay' => $paymentWay]);
                } else {
                    $error = 'Coś poszło nie tak';
                }
            }

            if (!empty($requestArray) && $submit == 1) {
                $selectedSeatsIdArray = explode(",", $requestArray['seatsIds']);
                $selectedTicketsIdArray = explode(",", $requestArray['ticketsIds']);
                $selectedVouchersIdArray = explode(",", $requestArray['vouchersIds']);
                $selectedPromotionId = $requestArray['promotionId'];
                $selectedPaymentWayId = $requestArray['paymentId'];

                if ($this->validSeat($selectedSeatsIdArray, $roomLayout) &&
                    $this->validTickets($selectedTicketsIdArray, $id) &&
                    $this->validVoucher($selectedVouchersIdArray) &&
                    (!$selectedPromotionId ||
                        $this->getDoctrine()->getRepository(Promocje::class)->getPromotionToCheck($selectedPromotionId)) &&
                    ($selectedPaymentWayId == 1 || $selectedPaymentWayId == 2)) {
                    $transaction = $this->addToDatabase($seance, $selectedSeatsIdArray, $selectedTicketsIdArray, $selectedVouchersIdArray, $selectedPromotionId, $selectedPaymentWayId);
                    return $this->redirectToRoute('workers_app/transactions/end', ['id' => $transaction->getId()]);
                } else {
                    $error = 'Coś poszło nie tak';
                }
            }

            if (!empty($requestArray) && $submit == 2) {
                $selectedSeatsIdArray = explode(",", $requestArray['seatsIds']);
            }

            $rowType = $this->getDoctrine()->getRepository(Typyrzedow::class)->findAll();
            return $this->render('workersApp/transactions/add.html.twig', ['seance' => $seance, 'error' => $error,
                'roomLayout' => $roomLayout, 'tickets' => $tickets, 'selectedSeats' => $selectedSeatsIdArray, 'rowType' => $rowType]);
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/reservations/accomplish/{id<[1-9]\d*>?1}", name="workers_app/reservations/accomplish", methods={"GET", "POST"})
     */
    public function accomplishReservation(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $requestArray = $request->request->all();
            if ((!$reservation = $this->getDoctrine()->getRepository(Rezerwacje::class)->find($id) and empty($requestArray)) or ($reservation and $reservation->isSfinalizowana())) {
                return $this->redirectToRoute('workers_app/no_permission');
            }

            $reservationsSeats = $this->getSeatsForReservation($reservation);
            $error = null;
            if (!empty($requestArray)) {
                $seatsIdArrayFromClient = explode(",", $requestArray['seatsIds']);
                $ticketsIdArrayFromClient = explode(",", $requestArray['ticketsIds']);
                $vouchersIdArrayFromClient = explode(",", $requestArray['vouchersIds']);
                $promotionIdFromClient = $requestArray['promotionId'];
                $paymentWayIdFromClient = $requestArray['paymentId'];
                $seanceId = $requestArray['seanceId'];

                $seance = $this->getDoctrine()->getRepository(Seanse::class)->find($seanceId);

                if ($this->validSeanceId($seance, $reservation) &&
                    $this->validSeatForReservationAccomplish($seatsIdArrayFromClient, $reservationsSeats, $seance) &&
                    $this->validTickets($ticketsIdArrayFromClient, $seanceId) &&
                    $this->validVoucher($vouchersIdArrayFromClient) &&
                    (!$promotionIdFromClient ||
                        $this->getDoctrine()->getRepository(Promocje::class)->getPromotionToCheck($promotionIdFromClient)) &&
                    ($paymentWayIdFromClient == 1 || $paymentWayIdFromClient == 2)) {

                    $transaction = $this->addToDatabase($seance, $seatsIdArrayFromClient, $ticketsIdArrayFromClient, $vouchersIdArrayFromClient, $promotionIdFromClient, $paymentWayIdFromClient);
                    $entityManager = $this->getDoctrine()->getManager();
                    if($reservation && $this->checkIfReservationDone($seatsIdArrayFromClient, $reservationsSeats)) {
                        $reservation->setSfinalizowana(1);
                    }

                    $entityManager->merge($transaction);
                    $entityManager->flush();
                    return $this->redirectToRoute('workers_app/transactions/end', ['id' => $transaction->getId()]);
                } else {
                    $error = 'Coś poszło nie tak';
                }
            }

            if(!$reservation){
                return $this->redirectToRoute('workers_app/no_permission');
            }

            $promotions = $this->getDoctrine()->getRepository(Promocje::class)->findCurrent();
            $paymentWay = $this->getDoctrine()->getRepository(Rodzajeplatnosci::class)->findAll();
            return $this->render('workersApp/transactions/accomplish.html.twig', [
                'reservation' => $reservation, 'promotions' => $promotions, 'error' => $error,
                'selectedSeats' => $reservationsSeats, 'paymentWay' => $paymentWay]);
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    private function getSeatsForReservation($reservation)
    {
        if(!$reservation){
            return false;
        }
        $seats = $this->getDoctrine()->getRepository(Miejsca::class)->getSeatsForReservation($reservation->getId());
        $seance = $reservation->getSeanse();
        $selectedSeats = [];
        foreach ($seats AS $seat) {
            $selectedSeats[$seat->getId()] = array(
                'miejsca' => $seat,
                'status' => 1);
        }
        foreach ($seance->getTranzakcje()->getIterator() AS $transaction) {
            foreach ($transaction->getBilety()->getIterator() AS $ticket) {
                if (!$ticket->getCzyanulowany()) {
                    $transSeat = $ticket->getMiejsca();
                    $seatId = $transSeat->getId();
                    if(array_key_exists($seatId, $selectedSeats)) {
                        $selectedSeats[$seatId]['status'] = 0;
                        $selectedSeats[$seatId]['cena'] = $ticket->getCena();
                        $selectedSeats[$seatId]['rodzajbiletu'] = $ticket->getRodzajebiletow();
                    }
                }
            }
        }
        return $selectedSeats;
    }

    private function validSeanceId($seance, $reservation){
        if(!$seance){
            return false;
        }
        if($reservation && $seance != $reservation->getSeanse()){
            return false;
        }
        return true;
    }

    private function addToDatabase($seance, $selectedSeatsIdArray, $selectedTicketsIdArray, $selectedVouchersIdArray, $selectedPromotionId, $selectedPaymentWayId)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $transaction = new Tranzakcje();
        $date = new \DateTime(date('Y-m-d H:i:s'));
        $transaction->setData($date);
        $transaction->setRodzajeplatnosci($this->getDoctrine()->getRepository(Rodzajeplatnosci::class)->find($selectedPaymentWayId));
        $transaction->setSeanse($seance);
        $transaction->setPracownicy($this->getUser());
        $promotion = $this->getDoctrine()->getRepository(Promocje::class)->find($selectedPromotionId);
        if ($promotion) $transaction->setPromocje($promotion);
        $transaction->setCzyodwiedzajacy(0);
        $ticketsArrayCollection = new ArrayCollection();
        $pmrbs = [];
        foreach ($seance->getPulebiletow()->getPulaMaRodzajeBiletow()->getIterator() as $pmrb) {
            $pmrbs[$pmrb->getId()] = $pmrb;
        }
        for ($i = 0; $i < count($selectedSeatsIdArray); $i++) {
            $ticket = new Bilety();
            $ticketType = $pmrbs[$selectedTicketsIdArray[$i]];;
            $voucher = $this->getDoctrine()->getRepository(Vouchery::class)->find($selectedVouchersIdArray[$i]);
            $ticket->setCena($this->calculatePrice($ticketType, $promotion, $voucher));
            $ticket->setLosowecyfry(rand(100, 999));
            $ticket->setCyfrakontrolna(0);
            $ticket->setTranzakcje($transaction);
            $ticket->setRodzajebiletow($ticketType->getRodzajebiletow());
            $ticket->setMiejsca($this->getDoctrine()->getRepository(Miejsca::class)->find($selectedSeatsIdArray[$i]));
            if ($voucher) {
                $ticket->setVouchery($voucher);
                $voucher->setCzywykorzystany(1);
            }
            $ticketsArrayCollection->add($ticket);
            $entityManager->persist($ticket);
        }
        $transaction->setBilety($ticketsArrayCollection);
        $entityManager->persist($transaction);
        $entityManager->flush();

        $ticketsWithoutControlNumber = $this->getDoctrine()->getRepository(Bilety::class)->findBy(['tranzakcje' => $transaction]);
        foreach ($ticketsWithoutControlNumber as $ticketWithoutControlNumber) {
            $ticketWithoutControlNumber->recalculateControlDigit();
            $entityManager->persist($ticketWithoutControlNumber);
        }
        $entityManager->flush();

        return $transaction;
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
                    if ($roomLayout[$transSeat->getId()]['status'] == 1 or $roomLayout[$transSeat->getId()]['status'] == 2) {
                        $roomLayout[$transSeat->getId()]['status'] = 4;
                    }
                }
            }
        }
        foreach ($seance->getRezerwacje()->getIterator() AS $booking) {
            if (!$booking->isSfinalizowana()) {
                foreach ($booking->getMiejsca()->getIterator() AS $revSeat) {
                    if ($roomLayout[$revSeat->getId()]['status'] == 1 or $roomLayout[$revSeat->getId()]['status'] == 2) {
                        $roomLayout[$revSeat->getId()]['status'] = 3;

                    }
                }
            }
        }

        return $roomLayout;
    }

    private function calculatePrice($ticketType, $promotion, $voucher)
    {
        $price = $ticketType->getCena();
        if ($promotion) {
            if ($promotion->isCzykwotowa()) {
                $price -= $promotion->getWartosc();
            } else {
                $price -= $price * ($promotion->getWartosc() / 100);
            }
        }
        if ($voucher) {
            if ($voucher->isCzykwotowa()) {
                $price -= $voucher->getWartosc();
            } else {
                $price -= $price * ($voucher->getWartosc() / 100);
            }
        }
        if ($price < 0) {
            $price = 0;
        }
        return $price;
    }

    private function validTickets($ticketsIdArray, $seanceId)
    {
        foreach ($ticketsIdArray as $ticketId) {
            if (!preg_match('/^[0-9]{1,}$/', $ticketId)) {
                return false;
            }
            $ticketToCheck = $this->getDoctrine()->getRepository(Pulebiletow::class)->getTicketToCheck($seanceId, $ticketId);
            if (!$ticketToCheck) {
                return false;
            }
        }
        return true;
    }

    private function validVoucher($voucherIdArray)
    {
        if (count(array_filter(array_unique($voucherIdArray))) != count(array_filter($voucherIdArray))) {
            return false;
        }
        foreach ($voucherIdArray as $voucherId) {
            if ($voucherId) {
                if (!preg_match('/^[0-9]{1,}$/', $voucherId)) {
                    return false;
                }
                $voucher = $this->getDoctrine()->getRepository(Vouchery::class)->find($voucherId);

                if (!$voucher) {
                    return false;
                }
                if ($voucher->getCzywykorzystany()) {
                    return false;
                }
                if ($voucher->getKoniecpromocji()->format('Y-m-d') < date("Y-m-d")) {
                    return false;
                }
                if ($voucher->getPoczatekpromocji()->format('Y-m-d') > date("Y-m-d")) {
                    return false;
                }
            }
        }
        return true;
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

            if ($roomLayout[$seatId]['status'] != 1 and $roomLayout[$seatId]['status'] != 2) {
                return false;
            }
        }
        return true;
    }

    private function validSeatForReservationAccomplish($seatsIdArrayFromClient, $reservationsSeats, $seance)
    {
        if($reservationsSeats){
            if (count($seatsIdArrayFromClient) > count($reservationsSeats)) {
                return false;
            }

            foreach ($seatsIdArrayFromClient as $seatId) {
                if (!preg_match('/^[0-9]{1,}$/', $seatId)) {
                    return false;
                }

                if (!array_key_exists($seatId, $reservationsSeats)) {
                    return false;
                }

                if ($reservationsSeats[$seatId]['status'] != 1) {
                    return false;
                }
            }
        } else {
            $roomLayout = $this->getRoomLayout($seance);
            return $this->validSeat($seatsIdArrayFromClient, $roomLayout);
        }
        return true;
    }

    private function checkIfReservationDone($seatsIdArrayFromClient, $reservationsSeats){
        $soldSeatsCounter = 0;
        foreach ($reservationsSeats as $reservationsSeat){
            if(!$reservationsSeat['status']){
                $soldSeatsCounter++;
            }
        }
        $soldSeatsCounter += count($seatsIdArrayFromClient);

        if($soldSeatsCounter == count($reservationsSeats)){
            return true;
        }
        return false;
    }

    /**
     * @Route("/transaction/check_voucher/{id<[1-9]\d*>?}", name="workers_app/transactions/check_voucher", methods={"POST"})
     */
    function checkVoucherByCode(Request $request, $id)
    {
        $voucherCode = $request->get('voucherCode');

        if (strlen($voucherCode) < 28 || !preg_match('/^[0-9]{1,}$/', $voucherCode) || !Vouchery::verifyCode($voucherCode)) {
            return new JsonResponse(['error' => 'Błędny kod vouchera']);
        }
        $voucherId = $this->getDoctrine()->getRepository(Vouchery::class)->findVoucherByCode($voucherCode);
        if (!$voucherId) {
            return new JsonResponse(['error' => 'Voucher o podanym kodzie nie istnieje']);
        }

        $voucher = $this->getDoctrine()->getRepository(Vouchery::class)->find($voucherId);

        if ($voucher->getCzywykorzystany()) {
            return new JsonResponse(['error' => 'Voucher o podanym kodzie został już wykorzystany.']);
        }
        if ($voucher->getKoniecpromocji()->format('Y-m-d') < date("Y-m-d")) {
            return new JsonResponse(['error' => 'Voucher o podanym kodzie utracił swoją ważność.']);
        }

        if ($voucher->getPoczatekpromocji()->format('Y-m-d') > date("Y-m-d")) {
            return new JsonResponse(['error' => 'Voucher o podanym kodzie nie jest jeszcze aktywny.']);
        }

        $voucherObj = [
            'id' => $voucher->getId(),
            'czykwotowa' => $voucher->isCzykwotowa(),
            'wartosc' => $voucher->getWartosc()
        ];

        return new JsonResponse($voucherObj);
    }

    /**
     * @Route("/transaction/end/{id<[1-9]\d*>?}", name="workers_app/transactions/end", methods={"GET", "POST"})
     */
    function showEndInfo($id)
    {
        $transaction = $this->getDoctrine()->getRepository(Tranzakcje::class)->find($id);
        return $this->render('workersApp/transactions/end.html.twig', ['transactionId' => $transaction->getId()]);
    }

    /**
     * @Route("/ticket/{id<[1-9]\d*>?}", name="workers_app/ticket", methods={"GET", "POST"})
     */
    function createTicket($id)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
                return $this->redirectToRoute('workers_app/logout_page');
            }
            $entityManager = $this->getDoctrine()->getManager();
            $transaction = $entityManager->getRepository(Tranzakcje::class)->find($id);
            $snappy = $this->get('knp_snappy.pdf');
            $html = $this->renderView('workersApp/ticket/ticket.html.twig', ['transaction' => $transaction]);
            return new Response(
                $snappy->getOutputFromHtml($html),
                200,
                array(
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="ticket.pdf"'
                )
            );
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/verify_ticket", name="workers_app/verify_ticket", methods={"GET","POST"})
     */
    function verifyTicket(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($request->request->all()) {
                if (is_a($ticketOrError = $this->checkTicket($ticketCode = $request->get('ticketCode')), 'App\Entity\Bilety')) {
                    $ticketOrError->setCzywykorzystany(1);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($ticketOrError);
                    $entityManager->flush();
                    return new JsonResponse(['message' => 'Weryfikacja biletu powiodła się.']);
                } else {
                    return new JsonResponse($ticketOrError);
                }
            }
            return $this->render('workersApp/transactions/verifyTicket.html.twig');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/return_ticket", name="workers_app/return_ticket", methods={"GET","POST"})
     */
    function returnTicket(Request $request)
    {
        if ($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            if ($request->request->all()) {
                if (is_a($ticketOrError = $this->checkTicket($ticketCode = $request->get('ticketCode')), 'App\Entity\Bilety')) {
                    $ticketOrError->setCzyanulowany(1);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($ticketOrError);
                    $entityManager->flush();
                    return new JsonResponse(['message' => 'Zwrot biletu powiódł się.']);
                } else {
                    return new JsonResponse($ticketOrError);
                }
            }
            return $this->render('workersApp/transactions/returnTicket.html.twig');
        } else {
            return $this->redirectToRoute('workers_app/no_permission');
        }
    }


    private function checkTicket($ticketCode)
    {

        if (strlen($ticketCode) < 28 || !preg_match('/^[0-9]{1,}$/', $ticketCode) || !Bilety::verifyCode($ticketCode)) {
            return ['error' => 'Błędny kod biletu'];
        }
        $ticketId = $this->getDoctrine()->getRepository(Bilety::class)->findTicketByCode($ticketCode);
        if (!$ticketId) {
            return ['error' => 'Bilet o podanym kodzie nie istnieje'];
        }

        $ticket = $this->getDoctrine()->getRepository(Bilety::class)->find($ticketId);

        if ($ticket->getCzywykorzystany()) {
            return ['error' => 'Bilet o podanym kodzie został już wykorzystany.'];
        }

        if ($ticket->getCzyanulowany()) {
            return ['error' => 'Bilet o podanym kodzie został anulowany.'];
        }

        return $ticket;
    }
}
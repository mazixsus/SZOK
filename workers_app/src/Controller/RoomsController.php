<?php
/**
 * Created by PhpStorm.
 * User: Mateusz
 * Date: 28.10.2018
 * Time: 17:25
 */

namespace App\Controller;


use App\Entity\Miejsca;
use App\Entity\Rzedy;
use App\Entity\Sale;
use App\Entity\Typyrzedow;
use App\Entity\Seanse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\SaleRepository;


class RoomsController extends Controller
{
    /**
     * @Route("/screeningRooms/{page<[1-9]\d*>?1}", name="workers_app/rooms_page", methods={"GET", "POST"})
     */
    public function index($page)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Sale::class)->getPageCount($pageLimit);

            if ($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('workers_app/rooms_page');
            else {
                $rooms = $this->getDoctrine()->getRepository(Sale::class)->findRooms($page, $pageLimit);
                $seatCount = $this->getDoctrine()->getRepository(Miejsca::class)->getSeatsCount($page, $pageLimit);
                $checkRooms = $this->getDoctrine()->getRepository(Seanse::class)->checkSeancesForRooms($rooms);
                return $this->render('workersApp/screeningRooms/list.html.twig', array('rooms' => $rooms, 'seatCounts' => $seatCount, 'checkRooms' => $checkRooms, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/screeningRooms/new", name="workers_app/rooms_page/room_creator_page", methods={"GET", "POST"})
     */
    public function new(Request $request)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager = $this->getDoctrine()->getManager();
            if ($this->ifRoomViewExist($request)) {
                $rowCount = $request->get('rowCount');
                $seatCount = $request->get('seatCount');
                $rowCode = $request->get('rowCode');
                $seatCode = $request->get('seatCode');
                $roomNumber = $request->get('roomNumber');
                $seatCodeArray = explode(",", $seatCode);
                if ($entityManager->getRepository(Sale::class)->findOneBy(array('numersali' => $roomNumber))) {
                    $error = 'Sala o podanym numerze już istnieje. Zmień numer sali.';
                    $values = array(
                        "rowCount" => $rowCount,
                        "seatCount" => $seatCount,
                        "rowCode" => $rowCode,
                        "seatCode" => $seatCode,
                        "roomNumber" => $roomNumber
                    );
                } else {
                    if ($this->checkRoomViewRequest($rowCount, $seatCount, $rowCode, $seatCodeArray, $roomNumber)) {
                        $this->pushRoom($rowCount, $seatCount, $rowCode, $seatCodeArray, $roomNumber);
                        return $this->redirectToRoute('workers_app/rooms_page');
                    } else {
                        $error = 'Wprowadzone dane są niepoprawne';
                        $values = array(
                            "rowCount" => $rowCount,
                            "seatCount" => $seatCount,
                            "rowCode" => $rowCode,
                            "seatCode" => $seatCode,
                            "roomNumber" => $roomNumber
                        );
                    }
                }
            } else {
                $values = array(
                    "rowCount" => "10",
                    "seatCount" => "10",
                    "rowCode" => "1111111111",
                    "seatCode" => "1111111111,1111111111,1111111111,1111111111,1111111111,1111111111,1111111111,1111111111,1111111111,1111111111",
                    "roomNumber" => ""
                );
                $error = "";
            }
            $rowType = $entityManager->getRepository(Typyrzedow::class)->findAll();
            return $this->render('workersApp/screeningRooms/new.html.twig', ['error' => $error, 'values' => $values, 'rowType' => $rowType]);

        } else if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    private function ifRoomViewExist(Request $request)
    {
        if ($request->request->has("rowCount")
            and $request->request->has("seatCount")
            and $request->request->has("rowCode")
            and $request->request->has("seatCode")
            and $request->request->has("roomNumber")) {
            return true;
        }
        return false;
    }

    private function checkRoomViewRequest($rowCount, $seatCount, $rowCode, $seatCodeArray, $roomNumber)
    {
        if (!preg_match('/^[a-zA-Z0-9]{1,3}$/', $roomNumber))
            return false;
        if ($rowCount != strlen($rowCode))
            return false;
        if ($rowCount != count($seatCodeArray))
            return false;
        for ($i = 0; $i < $rowCount; $i++) {
            if (strlen($seatCodeArray[$i]) != $seatCount) {
                return false;
            }
            if ($rowCode[$i] != 1 && $rowCode[$i] != 2) {
                return false;
            }
            for ($j = 0; $j < $seatCount; $j++) {
                if ($seatCodeArray[$i][$j] != 1 && $seatCodeArray[$i][$j] != 0) {
                    return false;
                }
            }
        }
        return true;
    }

    private function pushRoom($rowCount, $seatCount, $rowCode, $seatCodeArray, $roomNumber)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $room = new Sale();
        $room->setNumersali($roomNumber);
        $room->setDlugoscsali($rowCount);
        $room->setSzerokoscsali($seatCount);
        $entityManager->persist($room);
        for ($i = 0; $i < $rowCount; $i++) {
            $row = new Rzedy();
            $row->setNumerrzedu($i + 1);
            $rowType = $entityManager->getRepository(Typyrzedow::class)->find($rowCode[$i]);
            $row->setTypyrzedow($rowType);
            $row->setSale($room);
            $entityManager->persist($row);
            $this->pushSeatsInRow($row, $seatCount, $seatCodeArray[$i], $entityManager);
        }
        $entityManager->flush();
    }

    private function pushSeatsInRow($row, $seatCount, $seatCode, $entityManager)
    {
        $seatNumber = 1;
        for ($j = 0; $j < $seatCount; $j++) {
            $seat = new Miejsca();
            $seat->setPozycja($j + 1);
            if ($seatCode[$j] == 1) {
                $seat->setNumermiejsca($seatNumber);
                $seatNumber++;
            } else {
                $seat->setNumermiejsca(0);
            }
            $seat->setRzedy($row);
            $entityManager->persist($seat);
        }
    }

    /**
     * @Route("/screeningRooms/view/{id<[1-9]\d*>?}", name="workers_app/rooms_page/view", methods={"GET", "POST"})
     */
    public function view($id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        $entityManager = $this->getDoctrine()->getManager();
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($room = $this->getDoctrine()->getRepository(Sale::class)->find($id)) {
                $values = $this->getRoomView($id);
                $seatCount = $this->getDoctrine()->getRepository(Miejsca::class)->getSeatsCountOfCurrent($id);
                if ($entityManager->getRepository(Seanse::class)->findOneBy(array('sale' => $id))) {
                    $checkRoom = false;
                } else {
                    $checkRoom = true;
                }
                $rowType = $entityManager->getRepository(Typyrzedow::class)->findAll();
                return $this->render('workersApp/screeningRooms/view.html.twig', ['id' => $id, 'seatCount' => $seatCount,
                    'checkRoom' => $checkRoom, 'values' => $values, 'rowType' => $rowType]);
            } else {
                return $this->redirectToRoute('workers_app/rooms_page');
            }
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    public function getRoomView($id)
    {
        $room = $this->getDoctrine()->getRepository(Sale::class)->find($id);
        $rowCount = $room->getDlugoscsali();
        $seatCount = $room->getSzerokoscsali();
        $roomNumber = $room->getNumersali();

        $rowCode = "";
        $seatCode = "";
        $rows = $this->getDoctrine()->getRepository(Rzedy::class)->getRows($id);
        for ($i = 0; $i < $rowCount; $i++) {

            $rowType = $rows[$i]->getTypyrzedow();
            $rowCode .= (string)$rowType->getId();

            if ($i != 0)
                $seatCode .= ",";

            $rowId = $rows[$i]->getId();
            $seats = $this->getDoctrine()->getRepository(Miejsca::class)->getSeats($rowId);
            for ($j = 0; $j < $seatCount; $j++) {
                if ($seats[$j]->getNumermiejsca() == 0)
                    $seatCode .= 0;
                else {
                    $seatCode .= 1;
                }
            }
        }
        $values = array(
            "rowCount" => $rowCount,
            "seatCount" => $seatCount,
            "rowCode" => $rowCode,
            "seatCode" => $seatCode,
            "roomNumber" => $roomNumber
        );
        return $values;
    }

    /**
     * @Route("/worekrsApp/screeningRooms/edit/{id<[1-9]\d*>?}", name="workers_app/rooms_page/edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        $entityManager = $this->getDoctrine()->getManager();
        if ($this->isGranted('ROLE_ADMIN')) {
            if ($this->ifRoomViewExist($request)) {
                $rowCount = $request->get('rowCount');
                $seatCount = $request->get('seatCount');
                $rowCode = $request->get('rowCode');
                $seatCode = $request->get('seatCode');
                $roomNumber = $request->get('roomNumber');
                $seatCodeArray = explode(",", $seatCode);
                $values = array(
                    "rowCount" => $rowCount,
                    "seatCount" => $seatCount,
                    "rowCode" => $rowCode,
                    "seatCode" => $seatCode,
                    "roomNumber" => $roomNumber
                );
                $roomToCheck = $entityManager->getRepository(Sale::class)->findOneBy(array('numersali' => $roomNumber));
                if ($roomToCheck && $roomToCheck->getId() != $id) {
                    $error = 'Sala o podanym numerze już istnieje. Zmień numer sali.';
                } else {
                    if ($this->checkRoomViewRequest($rowCount, $seatCount, $rowCode, $seatCodeArray, $roomNumber)) {
                        $this->updateRoom($id, $rowCount, $seatCount, $rowCode, $seatCodeArray, $roomNumber);
                        return $this->redirectToRoute('workers_app/rooms_page/view', ['id' => $id]);
                    } else {
                        $error = 'Wprowadzone dane są niepoprawne.';
                    }
                }
            } else {
                if ($room = $this->getDoctrine()->getRepository(Sale::class)->find($id)) {
                    $values = $this->getRoomView($id);
                    $error = null;
                } else {
                    return $this->redirectToRoute('workers_app/no_permission');
                }
            }
            $rowType = $entityManager->getRepository(Typyrzedow::class)->findAll();
            $editable = true;
            if ($entityManager->getRepository(Seanse::class)->findOneBy(array('sale' => $id))) {
                $editable = false;
            }
            return $this->render('workersApp/screeningRooms/edit.html.twig', ['error' => $error, 'id' => $id, 'values' => $values, 'rowType' => $rowType, 'editable' => $editable]);

        } else if ($this->isGranted('ROLE_MANAGER')) {
            if ($this->ifRoomViewExist($request)) {
                $rowCode = $request->get('rowCode');
                if ($this->checkChangedRows($id, $rowCode)) {
                    $this->updateRoomRows($id, $rowCode);
                    return $this->redirectToRoute('workers_app/rooms_page/view', ['id' => $id]);
                }
            }

            if ($room = $this->getDoctrine()->getRepository(Sale::class)->find($id)) {
                $values = $this->getRoomView($id);
                $error = null;
            } else {
                return $this->redirectToRoute('workers_app/rooms_page');
            }
            $rowType = $entityManager->getRepository(Typyrzedow::class)->findAll();
            return $this->render('workersApp/screeningRooms/editManager.html.twig', ['error' => $error, 'id' => $id, 'values' => $values, 'rowType' => $rowType]);

        } else if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    private function updateRoomRows($id, $rowCode)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $room = $this->getDoctrine()->getRepository(Sale::class)->find($id);
        $rowCount = $room->getDlugoscsali();
        $rows = $this->getDoctrine()->getRepository(Rzedy::class)->getRows($id);
        for ($i = 0; $i < $rowCount; $i++) {
            $rowType = $entityManager->getRepository(Typyrzedow::class)->find($rowCode[$i]);
            $rows[$i]->setTypyrzedow($rowType);
        }
        $entityManager->flush();
    }

    private function checkChangedRows($id, $rowCode)
    {
        $room = $this->getDoctrine()->getRepository(Sale::class)->find($id);
        $rowCount = $room->getDlugoscsali();
        if ($rowCount != strlen($rowCode))
            return false;
        for ($i = 0; $i < $rowCount; $i++) {
            if ($rowCode[$i] != 1 && $rowCode[$i] != 2) {
                return false;
            }
        }
        return true;
    }

    private function updateRoom($id, $rowCount, $seatCount, $rowCode, $seatCodeArray, $roomNumber)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $room = $this->getDoctrine()->getRepository(Sale::class)->find($id);
        $room->setDlugoscsali($rowCount);
        $room->setSzerokoscsali($seatCount);
        $room->setNumersali($roomNumber);
        $entityManager->merge($room);

        $existRows = $this->getDoctrine()->getRepository(Rzedy::class)->getRows($id);

        if (count($existRows) > $rowCount) {
            $rows = array_slice($existRows, 0, $rowCount);
            $rowsToRemove = array_slice($existRows, $rowCount, count($existRows) - $rowCount);
            foreach($rowsToRemove as $rowToRemove){
                foreach($rowToRemove->getMiejsca()->getIterator() as $seat){
                    $entityManager->remove($seat);
                }
                $entityManager->remove($rowToRemove);
            }
        } else {
            $rows = $existRows;
            for ($i = 0; $i < $rowCount - count($existRows); $i++) {
                $rows[] = new Rzedy();
            }
        }

        for ($i = 0; $i < $rowCount; $i++) {
            $rowType = $entityManager->getRepository(Typyrzedow::class)->find($rowCode[$i]);
            $rows[$i]->setTypyrzedow($rowType);
            $rows[$i]->setNumerrzedu($i + 1);
            if($rows[$i]->getId()){
                $rowId = $rows[$i]->getId();
                $existSeats = $this->getDoctrine()->getRepository(Miejsca::class)->getSeats($rowId);
                $this->updateSeatsInRow($rows[$i], $existSeats, $seatCount, $seatCodeArray[$i], $entityManager);
                $entityManager->merge($rows[$i]);
            } else {
                $rows[$i]->setSale($room);
                $entityManager->persist($rows[$i]);
                $this->pushSeatsInRow($rows[$i], $seatCount, $seatCodeArray[$i], $entityManager);

            }
        }
        $entityManager->flush();
    }

    private function updateSeatsInRow($row, $existSeats, $seatCount, $seatCode, $entityManager)
    {
        if (count($existSeats) > $seatCount) {
            $seats = array_slice($existSeats, 0, $seatCount);
            $seatsToRemove = array_slice($existSeats, $seatCount, count($existSeats) - $seatCount);
            foreach($seatsToRemove as $seatToRemove){
                $entityManager->remove($seatToRemove);
            }
        } else {
            $seats = $existSeats;
            for ($i = 0; $i < $seatCount - count($existSeats); $i++) {
                $seats[] = new Miejsca();
            }
        }

        $seatNumber = 1;
        for ($j = 0; $j < $seatCount; $j++) {
            $seats[$j]->setPozycja($j + 1);

            if ($seatCode[$j] == 1) {
                $seats[$j]->setNumermiejsca($seatNumber);
                $seatNumber++;
            } else {
                $seats[$j]->setNumermiejsca(0);
            }

            $seats[$j]->setRzedy($row);

            if($seats[$j]->getId()){
                $entityManager->merge($seats[$j]);
            } else {
                $entityManager->persist($seats[$j]);
            }
        }
    }

    /**
     * @Route("/screeningRooms/delete/{id<[1-9]\d*>?}", name="workers_app/rooms_page/delete", methods={"DELETE"})
     */
    public function delete($id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if ($this->isGranted('ROLE_ADMIN')) {
            $entityManager = $this->getDoctrine()->getManager();
            if (!$entityManager->getRepository(Seanse::class)->findOneBy(array('sale' => $id))) {
                $entityManager = $this->getDoctrine()->getManager();
                $room = $this->getDoctrine()->getRepository(Sale::class)->find($id);
                $rowCount = $room->getDlugoscsali();

                $rows = $this->getDoctrine()->getRepository(Rzedy::class)->getRows($id);

                for ($i = 0; $i < $rowCount; $i++) {
                    $rowId = $rows[$i]->getId();
                    $this->getDoctrine()->getRepository(Miejsca::class)->deleteSeat($rowId);
                }
                $this->getDoctrine()->getRepository(Rzedy::class)->deleteRows($id);
                $entityManager->remove($room);
                $entityManager->flush();
            }
        }
    }
}
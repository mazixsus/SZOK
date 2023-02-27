<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 19.11.2018
 * Time: 11:18
 */

namespace App\Controller;


use App\Entity\PulabiletowMaRodzajebiletow;
use App\Entity\Pulebiletow;
use App\Entity\Rodzajebiletow;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class TicketController extends AbstractController
{
    /**
     * @Route("/tickets/pools/show/{id}",
     *      name="workers_app/tickets/pools/show")
     */
    public function poolsHaveTypesOfTickets($id)
    {
        if ($this->isGranted('ROLE_ADMIN') OR ($this->isGranted('ROLE_MANAGER'))) {
        $pmr = $this->getDoctrine()->getRepository(PulabiletowMaRodzajebiletow::class)->findBy(array('pulebiletow' => $id),array('rodzajebiletow' => 'ASC'));
        $query = $this->getDoctrine()->getRepository(Pulebiletow::class)->find($id);
        $pula['nazwa'] = $query->getNazwa();
        $pula['status'] = $query->getUsunieto();
        $pula['id'] = $query->getID();
        if ($pula['status'] == null)
            $pula['status'] = true;
        return $this->render('workersApp\tickets\list.html.twig', array('query' => $pmr, 'pula' => $pula));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->render('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/pools/add",
     *      name="workers_app/tickets/pools/add",
     *      methods={"GET|POST"})
     */
    public function add(Request $request)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $rodzajeBiletow = $this->getDoctrine()->getRepository(Rodzajebiletow::class)->findAllActive();
            $nowaPula = new Pulebiletow();

            $form = $this->createFormBuilder($nowaPula)
                ->add('nazwa', TextType::class, array(
                    'attr' => array("class" => "form-control ",
                        'pattern' => '[A-Za-z0-9\-ĘÓĄŚŁŻŹĆŃęąóśłżźćń ]{3,45}',
                        'title' => 'Polskie litery, cyfry, spacje i myślniki, od 3 do 45 znaków',
                        'autocomplete' => "off"),
                    'csrf_protection' => false,
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Zapisz',
                    'attr' => array('class' => 'btn btn-primary float-right', "style" => "margin-right:-15px;")
                ))
                ->getForm();

            $form->handleRequest($request);
            $error = array();
            $wartosci = array();
            $empty='';

            foreach ($rodzajeBiletow as $id => $nazwa) {
                if (!isset($wartosci[$id])) {
                    array_push($wartosci, array(
                        'idRodzajBiletu' => $nazwa->getId(),
                        'nazwa' => $nazwa->getNazwa(),
                        'cena' => ''));
                }
            }
            if ($form->isSubmitted()) {
                $prices = $request->get('form_price');
                foreach ($rodzajeBiletow as $id => $nazwa) {
                    if (!isset($prices[$nazwa->getId()])) {
                        $prices[$nazwa->getId()] = '';
                    }
                }
                foreach ($rodzajeBiletow as $id => $nazwa) {
                    foreach ($prices as $key => $value) {
                        if ($wartosci[$id]['idRodzajBiletu'] == $key) {
                            $wartosci[$id] = array_replace($wartosci[$id], array('cena' => $value));
                        }
                    }
                }
                foreach ($prices as $key => $value) {
                    if ($value != '') {
                        if (!preg_match('/^\d+(?:\.\d{2})?$/', $value)) {
                            array_push($error, array('error' => 'Podana wartość jest nieprawidłowa.', 'id' => $key));
                        }
                        if ($value <= 0) {
                            array_push($error, array('error' => 'Podana wartość musi być liczbą większą od 0', 'id' => $key));
                        }
                    }
                }

                if ($form->isValid()) {
                    if ($prices and $keep = $request->get('form_keep')) {
                        if (!$error) {
                            $entityManager = $this->getDoctrine()->getManager();
                            $entityManager->persist($nowaPula);
                            foreach ($keep as $key => $value) {
                                $pulaMaRodzaj = new PulabiletowMaRodzajebiletow();
                                $pulaMaRodzaj->setCena($prices[$key]);
                                $pulaMaRodzaj->setRodzajebiletow($this->getDoctrine()->getRepository(Rodzajebiletow::class)->find($key));
                                $pulaMaRodzaj->setPulebiletow($nowaPula);
                                $entityManager->persist($pulaMaRodzaj);
                            }
                            $entityManager->flush();
                            return $this->redirectToRoute('workers_app/tickets/pools/show', array('id' => $nowaPula->getId()));
                        }
                        return $this->render('workersApp/tickets/add_edit.html.twig', array(
                            'form' => $form->createView(),
                            'errors' => $error,
                            'wartosci' => $wartosci,
                            'edit' => false,
                            'empty'=>$empty));
                    }
                    $empty = 'Przynajmniej jeden rodzaj biletu musi być wybrany';
                }
            }
            return $this->render('workersApp/tickets/add_edit.html.twig', array(
                'form' => $form->createView(),
                'errors' => $error,
                'wartosci' => $wartosci,
                'edit' => false,
                'empty'=>$empty));
        }else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->render('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/pools/edit/{id}",
     *      name="workers_app/tickets/pools/edit",
     *      methods={"GET|POST"})
     */

    public function edit(Request $request, $id)
    {
        {
            if ($this->isGranted('ROLE_ADMIN')) {
                $rodzajeBiletow = $this->getDoctrine()->getRepository(Rodzajebiletow::class)->findAllActive();
                $Pula = $this->getDoctrine()->getRepository(Pulebiletow::class)->find($pulaId=$id);

                $form = $this->createFormBuilder($Pula)
                    ->add('nazwa', TextType::class, array(
                        'attr' => array("class" => "form-control ",
                            'pattern' => '[A-Za-z0-9\-ĘÓĄŚŁŻŹĆŃęąóśłżźćń ]{3,45}',
                            'title' => 'Polskie litery, cyfry, spacje i myślniki, od 3 do 45 znaków',
                            'autocomplete' => "off"),
                        'csrf_protection' => false,
                        'label_attr' => array('class' => "col-sm-2 col-form-label")
                    ))
                    ->add('save', SubmitType::class, array(
                        'label' => 'Zapisz',
                        'attr' => array('class' => 'btn btn-primary float-right', "style" => "margin-right:-15px;")
                    ))
                    ->getForm();
                $prices = array();
                $wartosci = array();
                $error = array();
                $empty='';
                $bilety = $this->getDoctrine()->getRepository(PulabiletowMaRodzajebiletow::class)->findBy(array('pulebiletow' => $id));
                foreach ($bilety as $id => $nazwa) {
                    if (!isset($wartosci[ $nazwa->getRodzajebiletow()->getId()])) {
                       $wartosci[ $nazwa->getRodzajebiletow()->getId()] = array(
                            'idRodzajBiletu' => $nazwa->getRodzajebiletow()->getId(),
                            'nazwa' => $nazwa->getRodzajebiletow()->getNazwa(),
                            'cena' => $nazwa->getCena(),
                            'idPMR' => $nazwa->getId());
                    }
                }
                foreach ($rodzajeBiletow as $id => $nazwa) {
                    if (!isset($wartosci[$nazwa->getId()])) {
                        $wartosci[$nazwa->getId()] = array(
                            'idRodzajBiletu' => $nazwa->getId(),
                            'nazwa' => $nazwa->getNazwa(),
                            'cena' => null,
                            'idPMR' => null);
                    }
                    if (!isset($prices[$nazwa->getId()])) {
                        $prices[$nazwa->getId()] = null;
                    }
                }
                sort($wartosci);

                $form->handleRequest($request);
                if ($form->isSubmitted()) {
                    $prices = $request->get('form_price');
                    foreach ($rodzajeBiletow as $id => $nazwa) {
                        if (!isset($prices[$nazwa->getId()])) {
                            $prices[$nazwa->getId()] = null;
                        }
                    }
                    foreach ($rodzajeBiletow as $id => $nazwa) {
                        foreach ($prices as $key => $value) {
                            if ($wartosci[$id]['idRodzajBiletu'] == $key) {
                                $wartosci[$id] = array_replace($wartosci[$id], array('cena' => $value));
                            }
                        }
                    }
                    foreach ($prices as $key => $value) {
                        if ($value != '') {
                            if (!preg_match('/^\d+(?:\.\d{2})?$/', $value)) {
                                array_push($error, array('error' => 'Podana wartość jest nieprawidłowa.', 'id' => $key));
                            }
                            if ($value <= 0) {
                                array_push($error, array('error' => 'Podana wartość musi być liczbą większą od 0', 'id' => $key));
                            }
                        }
                    }
                    if ($form->isValid()) {
                        if ($prices and $keep = $request->get('form_keep')) {
                            if (!$error) {
                                $entityManager = $this->getDoctrine()->getManager();
                                $entityManager->persist($Pula);
                                foreach ($wartosci as $key => $value) {
                                    if(!is_null($wartosci[$key]['cena']) && !is_null($wartosci[$key]['idPMR']))
                                    {
                                        $pmr=$this->getDoctrine()->getRepository(PulabiletowMaRodzajebiletow::class)->find($wartosci[$key]['idPMR']);
                                        $pmr->setCena($wartosci[$key]['cena']);
                                        $entityManager->merge($pmr);
                                    }
                                    elseif(!is_null($wartosci[$key]['cena']) && is_null($wartosci[$key]['idPMR']))
                                    {
                                        $pmr=new PulabiletowMaRodzajebiletow();
                                        $pmr->setCena($wartosci[$key]['cena']);
                                        $pmr->setRodzajebiletow($this->getDoctrine()->getRepository(Rodzajebiletow::class)->find($wartosci[$key]['idRodzajBiletu']));
                                        $pmr->setPulebiletow($Pula);
                                        $entityManager->persist($pmr);
                                    }
                                    elseif(is_null($wartosci[$key]['cena']) && !is_null($wartosci[$key]['idPMR']))
                                    {
                                        $pmr=$this->getDoctrine()->getRepository(PulabiletowMaRodzajebiletow::class)->find($wartosci[$key]['idPMR']);
                                        $entityManager->remove($pmr);
                                    }
                                }
                                $entityManager->flush();
                                return $this->redirectToRoute('workers_app/tickets/pools/show', array('id' => $pulaId));
                            }
                            return $this->render('workersApp/tickets/add_edit.html.twig', array(
                                'form' => $form->createView(),
                                'ticketTypes' => $rodzajeBiletow,
                                'errors' => $error,
                                'wartosci' => $wartosci,
                                'edit' => true,
                                'id'=>$pulaId,
                                'empty'=>$empty));
                        }
                        $empty = 'Przynajmniej jeden rodzaj biletu musi być wybrany';
                    }
                }
                return $this->render('workersApp/tickets/add_edit.html.twig', array(
                    'form' => $form->createView(),
                    'ticketTypes' => $rodzajeBiletow,
                    'errors' => $error,
                    'wartosci' => $wartosci,
                    'edit' => true,
                    'id'=>$pulaId,
                    'empty'=>$empty
                ));
            } else {
                if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                    return $this->render('workersApp/mainPage/noPermission.html.twig');
                else
                    return $this->redirectToRoute('workers_app/login_page');
            }
        }
    }
}
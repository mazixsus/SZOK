<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 01.11.2018
 * Time: 14:01
 */

namespace App\Controller;

use App\Entity\Kategoriewiekowe;
use App\Entity\Pulebiletow;
use App\Entity\Rodzajebiletow;
use App\Entity\Rodzajefilmow;
use App\Entity\Rodzajeplatnosci;
use App\Entity\Role;
use App\Entity\Typyrzedow;
use App\Entity\Typyseansow;
use App\Entity\Wydarzeniaspecjalne;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DictionariesController extends AbstractController
{
    /**
     * @Route("/dictionaries",
     *     name="workers_app/dictionaries",
     *     methods={"GET"})
     *
     */
    public function index()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $list['roles'] = array(
                'name' => 'Role',
                'dictionaryName' => 'roles',
                'isCritical' => false);
            $list['typesOfRows'] = array(
                'name' => 'Typy Rzędów',
                'dictionaryName' => 'typesOfRows',
                'isCritical' => false);
            $list['ageCategories'] = array(
                'name' => 'Kategorie wiekowe',
                'dictionaryName' => 'ageCategories',
                'isCritical' => true);
            $list['typesOfFilms'] = array(
                'name' => 'Rodzaje filmów',
                'dictionaryName' => 'typesOfFilms',
                'isCritical' => true);
            $list['typesOfShows'] = array(
                'name' => 'Typy seansów',
                'dictionaryName' => 'typesOfShows',
                'isCritical' => true);
            $list['specialEvents'] = array(
                'name' => 'Wydarzenie specjalne',
                'dictionaryName' => 'specialEvents',
                'isCritical' => true);
            $list['typesOfPayments'] = array(
                'name' => 'Rodzaje płatności',
                'dictionaryName' => 'typesOfPayments',
                'isCritical' => false);
            return $this->render('workersApp/dictionaries/list.html.twig', array('list' => $list));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->render('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/{dictionaryName}/{currentPage<[1-9]\d*>?1}",
     *     name="workers_app/tickets/dictionaryName",
     *     requirements={"dictionaryName":"types|pools"},
     *     methods={"GET"})
     *
     *
     * @Route("/dictionaries/{dictionaryName}/{currentPage<[1-9]\d*>?1}",
     *     name="workers_app/dictionaries/dictionaryName",
     *     requirements={"dictionaryName":"roles|typesOfRows|ageCategories|typesOfFilms|typesOfShows|specialEvents|typesOfPayments"},
     *     methods={"GET"})
     *
     */
    public function list($dictionaryName, $currentPage, Request $request)
    {
        if ($this->isGranted('ROLE_ADMIN') OR ($this->isGranted('ROLE_MANAGER') AND $dictionaryName == 'pools')) {
            $var = $this->dictionary($dictionaryName);
            $slownik = $var['class'];
            $krytyczny = $var['isCritical'];
            $rodzaj = $var['displayName'];

            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository($slownik)->getPageCountOfActive($pageLimit);
            if ($currentPage > $pageCount and $pageCount != 0)
                if ($dictionaryName == 'type')
                    return $this->redirectToRoute("workers_app/tickets/dictionaryName", array('dictionaryName' => $dictionaryName));
                else
                    return $this->redirectToRoute("workers_app/dictionaries/dictionaryName", array('dictionaryName' => $dictionaryName));
            else {
                $pokaz = $this->getDoctrine()->getRepository($slownik)->findActive($currentPage, $pageLimit);
                return $this->render('workersApp/dictionaries/show.html.twig', array(
                    'slownik' => $pokaz, 'rodzaj' => $rodzaj, 'krytyczny' => $krytyczny, 'page' => $dictionaryName,
                    'currentPage' => $currentPage, 'pageCount' => $pageCount, 'deleted' => false));
            }
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->render('workersApp/mainPage/noPermission.html.twig');

            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/{dictionaryName}/add",
     *     name="workers_app/tickets/add",
     *     requirements={"dictionaryName":"types"},
     *     methods={"GET|POST"})
     *
     * @Route("/dictionaries/{dictionaryName}/add",
     *     name="workers_app/dictionaries/add",
     *     requirements={"dictionaryName":"roles|typesOfRows|ageCategories|typesOfFilms|typesOfShows|specialEvents|typesOfPayments"},
     *     methods={"GET|POST"})
     */
    public function add($dictionaryName, Request $request)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $var = $this->dictionary($dictionaryName);
            $type = $var['object'];
            $krytyczny = $var['isCritical'];
            $rodzaj = $var['displayName'];
            $form = $this->createFormBuilder($type)
                ->add('nazwa', TextType::class, array(
                    'attr' => array("class" => "form-control ",
                        'pattern' => '[A-Za-z0-9\-\/\+ĘÓĄŚŁŻŹĆŃęąóśłżźćń ]{2,45}',
                        'title' => 'Polskie litery, cyfry, spacje i myślniki, od 3 do 45 znaków',
                        'autocomplete' => "off"),
                    'label_attr' => array('class' => "col-sm-2 col-form-label")
                ))
                ->add('save', SubmitType::class, array(
                    'label' => 'Dodaj',
                    'attr' => array('class' => "btn btn-primary float-right", "style" => "margin-right:-15px;")
                ))
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($type);
                $entityManager->flush();
                if ($this->chceckTickets($dictionaryName))
                    return $this->redirectToRoute('workers_app/tickets/dictionaryName', array('dictionaryName' => $dictionaryName));
                else
                    return $this->redirectToRoute('workers_app/dictionaries/dictionaryName', array('dictionaryName' => $dictionaryName));
            }
            return $this->render('workersApp/dictionaries/add.html.twig', array(
                'krytyczny' => $krytyczny, 'form' => $form->createView(), 'page' => $dictionaryName, 'rodzaj' => $rodzaj));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->render('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/{dictionaryName}/deleted/{currentPage?1}",
     *     name="workers_app/tickets/deleted",
     *     requirements={"dictionaryName":"types|pools"},
     *     methods={"GET"})
     *
     *
     * @Route("/dictionaries/{dictionaryName}/deleted/{currentPage?1}",
     *     name="workers_app/dictionaries/deleted",
     *     requirements={"dictionaryName":"roles|typesOfRows|ageCategories|typesOfFilms|typesOfShows|specialEvents|typesOfPayments"},
     *     methods={"GET"})
     */
    public function deletedlist($dictionaryName, $currentPage, Request $request)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $var = $this->dictionary($dictionaryName);
            $slownik = $var['class'];
            $krytyczny = $var['isCritical'];
            $rodzaj = $var['displayName'];
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository($slownik)->getPageCountOfDeleted($pageLimit);
            if ($currentPage > $pageCount and $pageCount != 0)
                if ($this->chceckTickets($dictionaryName))
                    return $this->redirectToRoute("workers_app/tickets/dictionaryName", array('dictionaryName' => $dictionaryName));
                else
                    return $this->redirectToRoute("workers_app/dictionaries/dictionaryName", array('dictionaryName' => $dictionaryName));
            else {
                $pokaz = $this->getDoctrine()->getRepository($slownik)->findDeleted($currentPage, $pageLimit);
                return $this->render('workersApp/dictionaries/show.html.twig', array(
                    'slownik' => $pokaz, 'rodzaj' => $rodzaj, 'krytyczny' => $krytyczny, 'page' => $dictionaryName,
                    'currentPage' => $currentPage, 'pageCount' => $pageCount,
                    'deleted' => true));
            }
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->render('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/{dictionaryName}/edit{currentPage?1}/{id<[1-9]\d*>?}",
     *     name="workers_app/tickets/edit",
     *     requirements={"dictionaryName":"types|pools"},
     *     methods={"GET|POST"})
     *
     * @Route("/dictionaries/{dictionaryName}/edit/{currentPage?1}/{id<[1-9]\d*>?}",
     *     name="workers_app/dictionaries/edit",
     *     requirements={"dictionaryName":"roles|typesOfRows|ageCategories|typesOfFilms|typesOfShows|specialEvents|typesOfPayments"},
     *     methods={"GET|POST"})
     */
    public function edit(Request $request, $dictionaryName, $id, $currentPage)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $var = $this->dictionary($dictionaryName);
            $slownik = $var['class'];
            $krytyczny = $var['isCritical'];
            $rodzaj = $var['displayName'];

            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository($slownik)->getPageCountOfActive($pageLimit);
            if ($currentPage > $pageCount and $pageCount != 0)
                if ($this->chceckTickets($dictionaryName))
                    return $this->redirectToRoute("workers_app/tickets/dictionaryName", array('dictionaryName' => $dictionaryName));
                else
                    return $this->redirectToRoute("workers_app/dictionaries/dictionaryName", array('dictionaryName' => $dictionaryName));
            else {
                $pokaz = $this->getDoctrine()->getRepository($slownik)->findActive($currentPage, $pageLimit);
                $wartosc = $this->getDoctrine()->getRepository($slownik)->find($id);
                $form = $this->createFormBuilder($wartosc)
                    ->add('nazwa', TextType::class, array(
                        'attr' => array("class" => "form-control ",
                            'pattern' => '[A-Za-z0-9\/\+\-ĘÓĄŚŁŻŹĆŃęąóśłżźćń ]{2,45}',
                            'title' => 'Polskie litery, cyfry, spacje i myślniki, od 2 do 45 znaków',
                            'autocomplete' => "off"),
                    ))
                    ->add('save', SubmitType::class, array(
                        'label' => 'Zapisz',
                        'attr' => array('class' => "btn btn-sm btn-primary float-right")
                    ))
                    ->getForm();
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($wartosc);
                    $entityManager->flush();
                    if ($this->chceckTickets($dictionaryName))
                        return $this->redirectToRoute("workers_app/tickets/dictionaryName", array('dictionaryName' => $dictionaryName, 'currentPage' => $currentPage));
                    else
                        return $this->redirectToRoute("workers_app/dictionaries/dictionaryName", array('dictionaryName' => $dictionaryName, 'currentPage' => $currentPage));
                }
                return $this->render('workersApp/dictionaries/edit.html.twig', array(
                    'slownik' => $pokaz, 'krytyczny' => $krytyczny, 'form' => $form->createView(), 'page' => $dictionaryName,
                    'id' => $id, 'rodzaj' => $rodzaj, 'currentPage' => $currentPage,
                    'pageCount' => $pageCount, 'deleted' => false));
            }
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->render('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/{dictionaryName}/delete/{id<[1-9]\d*>?}",
     *     name="workers_app/tickets/delete",
     *      requirements={"dictionaryName":"types|pools"},
     *      methods={"DELETE"})
     *
     * @Route("/dictionaries/{dictionaryName}/delete/{id<[1-9]\d*>?}",
     *     name="workers_app/dictionaries/delete"),
     *     requirements={"dictionaryName":"roles|typesOfRows|ageCategories|typesOfFilms|typesOfShows|specialEvents|typesOfPayments"},
     * methods={"DELETE"})
     */
    public function delete($id, $dictionaryName)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $var = $this->dictionary($dictionaryName);
            $slownik = $var['class'];

            $wartosc = $this->getDoctrine()->getRepository($slownik)->find($id);
            $wartosc->setUsunieto(1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wartosc);
            $entityManager->flush();
            return $this->redirectToRoute('workers_app/main_page');
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/tickets/{dictionaryName}/restore/{id<[1-9]\d*>?}",
     *     name="workers_app/tickets/restore",
     *     requirements={"dictionaryName":"types|pools"},
     *     methods={"GET|POST"})
     *
     * @Route("/dictionaries/{dictionaryName}/restore/{id<[1-9]\d*>?}",
     *     name="workers_app/dictionaries/restore",
     *     requirements={"dictionaryName":"roles|typesOfRows|ageCategories|typesOfFilms|typesOfShows|specialEvents|typesOfPayments"},
     *     methods={"GET|POST"})
     */
    public function restore($id, $dictionaryName)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $var = $this->dictionary($dictionaryName);
            $slownik = $var['class'];
            $wartosc = $this->getDoctrine()->getRepository($slownik)->find($id);
            $wartosc->setUsunieto(null);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($wartosc);
            $entityManager->flush();
            if ($this->chceckTickets($dictionaryName))
                return $this->redirectToRoute("workers_app/tickets/dictionaryName", array('dictionaryName' => $dictionaryName));
            else
                return $this->redirectToRoute("workers_app/dictionaries/dictionaryName", array('dictionaryName' => $dictionaryName));
        } else {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY'))
                return $this->redirectToRoute('workersApp/mainPage/noPermission.html.twig');
            else
                return $this->redirectToRoute('workers_app/login_page');
        }
    }

    public function chceckTickets(string $dictionaryName)
    {
        if ($dictionaryName == 'types' || $dictionaryName == 'pools')
            return true;
        else
            return false;
    }

    public function dictionary(string $dictionaryName)
    {
        $params = array();
        switch ($dictionaryName) {
            case 'roles':
                $params['class'] = Role::class;
                $params['displayName'] = 'Role';
                $params['isCritical'] = false;
                break;
            case 'typesOfRows':
                $params['class'] = Typyrzedow::class;
                $params['displayName'] = 'Typy Rzędów';
                $params['isCritical'] = false;
                break;
            case 'ageCategories':
                $params['class'] = Kategoriewiekowe::class;
                $params['displayName'] = 'Kategorie Wiekowe';
                $params['isCritical'] = true;
                $params['object'] = new Kategoriewiekowe();
                break;
            case 'typesOfFilms':
                $params['class'] = Rodzajefilmow::class;
                $params['displayName'] = 'Rodzaje filmów';
                $params['isCritical'] = true;
                $params['object'] = new Rodzajefilmow();
                break;
            case 'typesOfShows':
                $params['class'] = Typyseansow::class;
                $params['displayName'] = 'Typy seansów';
                $params['isCritical'] = true;
                $params['object'] = new Typyseansow();
                break;
            case 'specialEvents':
                $params['class'] = Wydarzeniaspecjalne::class;
                $params['displayName'] = 'Wydarzenia specjalne';
                $params['isCritical'] = true;
                $params['object'] = new Wydarzeniaspecjalne();
                break;
            case 'typesOfPayments':
                $params['class'] = Rodzajeplatnosci::class;
                $params['displayName'] = 'Rodzaje płatności';
                $params['isCritical'] = false;
                break;
            case 'types':
                $params['class'] = Rodzajebiletow::class;
                $params['displayName'] = 'Rodzaje biletów';
                $params['isCritical'] = true;
                $params['object'] = new Rodzajebiletow();
                break;
            case 'pools':
                $params['class'] = Pulebiletow::class;
                $params['displayName'] = 'Pule Biletów';
                $params['isCritical'] = true;
                $params['object'] = new Pulebiletow();
                break;
        }
        return $params;
    }
}
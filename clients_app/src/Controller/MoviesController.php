<?php
/**
 * Created by PhpStorm.
 * User: gnowa
 * Date: 18.11.2018
 * Time: 13:55
 */

namespace App\Controller;

use App\Entity\Filmy;
use App\Entity\Kategoriewiekowe;
use App\Entity\Rodzajefilmow;
use App\Entity\Seanse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends Controller
{
    /**
     * @Route("/movies/{page<[1-9]\d*>?1},{movieType<[1-9]\d*>?}", name="clients_app/movies", methods={"GET", "POST"})
     * @Route("/", name="clients_app/main_page", methods={"GET", "POST"})
     */
    public function index(Request $request, $page = 1, $movieType = NULL)
    {
        if($this->isGranted('ROLE_USER') and AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }

        if($movieType) $movieType = $this->getDoctrine()->getRepository(Rodzajefilmow::class)->find($movieType);

        $defaultData = array(
            'movieType' => $movieType
        );

        $form = $this->getForm($defaultData);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $data = $form->getData()['movieType'];
            if($data) return $this->redirectToRoute('clients_app/movies', array(
                'page' => 1,
                'movieType' => $data->getId()
            ));
            else return $this->redirectToRoute('clients_app/movies');
        }

        $pageLimit = 8;
        $pageCount = $this->getDoctrine()->getRepository(Filmy::class)->getPageCountOfActual($pageLimit, $movieType);

        if($page > $pageCount and $pageCount != 0)
            return $this->redirectToRoute('clients_app/movies');
        else {
            $movies = $this->getDoctrine()->getRepository(Filmy::class)->findPageOfActual($page, $pageLimit, $movieType);
            $pathParams = array();
            if($movieType) $pathParams['movieType'] = $movieType->getId();
            return $this->render('clientsApp/movies/list.html.twig', array(
                'form' => $form->createView(),
                'movies' => $movies,
                'currentPage' => $page,
                'pageCount' => $pageCount,
                'pathName' => 'clients_app/movies',
                'pathParams' => $pathParams,
            ));
        }
    }

    /**
     * @Route("/movies/all/{page<[1-9]\d*>?1},{movieType<[1-9]\d*>?}", name="clients_app/movies/all", methods={"GET", "POST"})
     */
    public function listAll(Request $request, $page, $movieType)
    {
        if($this->isGranted('ROLE_USER') and AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        if($movieType) $movieType = $this->getDoctrine()->getRepository(Rodzajefilmow::class)->find($movieType);

        $defaultData = array(
            'movieType' => $movieType
        );

        $form = $this->getForm($defaultData);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $data = $form->getData()['movieType'];
            if($data) return $this->redirectToRoute('clients_app/movies/all', array(
                'page' => 1,
                'movieType' => $data->getId()
            ));
            else return $this->redirectToRoute('clients_app/movies/all');
        }

        $pageLimit = 8;
        $pageCount = $this->getDoctrine()->getRepository(Filmy::class)->getPageCount($pageLimit, $movieType);

        if($page > $pageCount and $pageCount != 0)
            return $this->redirectToRoute('clients_app/movies');
        else {
            $movies = $this->getDoctrine()->getRepository(Filmy::class)->findPage($page, $pageLimit, $movieType);
            $pathParams = array();
            if($movieType) $pathParams['movieType'] = $movieType->getId();
            return $this->render('clientsApp/movies/listAll.html.twig', array(
                'form' => $form->createView(),
                'movies' => $movies,
                'currentPage' => $page,
                'pageCount' => $pageCount,
                'pathName' => 'clients_app/movies/all',
                'pathParams' => $pathParams,
            ));;
        }
    }

    /**
     * @Route("/movies/show/{id<[1-9]\d*>}/{page<[1-9]\d*>?1},{filter<[1-9]\d*>?1}", name="clients_app/movies/show", methods={"GET", "POST"})
     */
    public function show(Request $request, $id, $page, $filter)
    {
        if($this->isGranted('ROLE_USER') and AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('clients_app/logout_page');
        }
        $movie = $this->getDoctrine()->getRepository(Filmy::class)->find($id);
        if(!$movie)
            return new NotFoundHttpException();
        if($request->get('form_date')) {
            if($form_date = \DateTime::createFromFormat('Y-m-d', $request->get('form_date'))) {
                return $this->redirectToRoute('clients_app/movies/show', array(
                    'id' => $id,
                    'page' => 1,
                    'filter' => $form_date->getTimestamp()
                ));
            }
        }

        $date = new \DateTime();
        if($filter > 1) {
            $date->setTimestamp($filter);
        }
        $date->setTime(0, 0, 0);

        $pageLimit = 5;
        $seancesPageCount = $this->getDoctrine()->getRepository(Seanse::class)
            ->getPageCountForMovie($movie, $date, $pageLimit);

        if($seancesPageCount > 0) {
            if($seancesPageCount < $page) $page = 1;
            $seancesPage = $this->getDoctrine()->getRepository(Seanse::class)
                ->findSeancesForMovie($movie, $date, $page, $pageLimit);
        } else {
            $seancesPage = NULL;
        }

        return $this->render('clientsApp/movies/show.html.twig', array(
            'movie' => $movie,
            'dateInput' => $date,
            'pageCount' => $seancesPageCount,
            'seancesPage' => $seancesPage,
            'currentPage' => $page,
            'pathName' => 'clients_app/movies/show',
            'pathParams' => array(
                'id' => $id,
                'filter' => $filter
            )
        ));
    }

    private function getForm(array $data)
    {
        return $this->createFormBuilder($data)
            ->add('movieType', EntityType::class, array(
                'class' => Rodzajefilmow::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'label' => 'Filtruj po gatunku:',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'placeholder' => 'Wszystkie',
                'attr' => array(
                    'class' => 'form-control mr-2 mb-2'
                ),
                'label_attr' => array(
                    'class' => 'mr-2 mb-2'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Filtruj',
                'attr' => array('class' => 'btn btn-primary mb-2')
            ))
            ->getForm();
    }
}
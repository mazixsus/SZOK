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
use App\Entity\Typyseansow;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MoviesController extends Controller
{
    /**
     * @Route("/movies/{page<[1-9]\d*>?1}", name="workers_app/movies", methods={"GET"})
     */
    public function index($page)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $pageLimit = $this->getParameter('page_limit');
            $pageCount = $this->getDoctrine()->getRepository(Filmy::class)->getPageCount($pageLimit);

            if($page > $pageCount and $pageCount != 0)
                return $this->redirectToRoute('workers_app/movies');
            else {
                $movies = $this->getDoctrine()->getRepository(Filmy::class)->findPage($page, $pageLimit);
                return $this->render('workersApp/movies/list.html.twig', array('movies' => $movies, 'currentPage' => $page, 'pageCount' => $pageCount));
            }
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/movies/new", name="workers_app/movies/new", methods={"GET", "POST"})
     */
    public function new(Request $request)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $movie = new Filmy();

            $form = $this->getForm($movie);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $movie = $form->getData();
                $file = $form['plakat']->getData();
                if($file != NULL) {
                    $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                    $file->move('../../images', $fileName);

                    $movie->setPlakat($fileName);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($movie);
                $entityManager->flush();

                return $this->redirectToRoute('workers_app/movies/show', array('id' => $movie->getId()));
            } else {

                return $this->render('workersApp/movies/new.html.twig', array('form' => $form->createView()));
            }
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/movies/edit/{id<[1-9]\d*>}", name="workers_app/movies/edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $movie = $this->getDoctrine()->getRepository(Filmy::class)->find($id);
            if(!$movie)
                return $this->redirectToRoute('workers_app/no_permission');

            $title = $movie->getTytul();
            $poster = $movie->getPlakat();
            $movie->setPlakat(NULL);

            $form = $this->getForm($movie);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) {
                $movie = $form->getData();
                if($poster == NULL or !(isset($_POST['keepPoster']) and $_POST['keepPoster'] == 1)) {
                    $file = $form['plakat']->getData();
                    if($file != NULL) {
                        $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
                        $file->move('../../images', $fileName);

                        if($poster and file_exists('../../images/' . $poster))
                            unlink('../../images/' . $poster);

                        $movie->setPlakat($fileName);
                    } else $movie->setPlakat($poster);
                }else $movie->setPlakat($poster);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($movie);
                $entityManager->flush();

                return $this->redirectToRoute('workers_app/movies/show', array('id' => $id));
            } else {

                return $this->render('workersApp/movies/edit.html.twig', array(
                    'form' => $form->createView(),
                    'title' => $title,
                    'poster' => $poster,
                    'id' => $id
                ));
            }
        } else if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('workers_app/no_permission');
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/movies/show/{id<[1-9]\d*>}/{page<[1-9]\d*>?1},{filter<[1-9]\d*>?1}", name="workers_app/movies/show", methods={"GET", "POST"})
     */
    public function show(Request $request, $id, $page, $filter)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $movie = $this->getDoctrine()->getRepository(Filmy::class)->find($id);
            if(!$movie)
                return $this->redirectToRoute('workers_app/no_permission');

            if($request->get('form_date')) {
                if($form_date = \DateTime::createFromFormat('Y-m-d', $request->get('form_date'))) {
                    return $this->redirectToRoute('workers_app/movies/show', array(
                       'id' => $id,
                       'page' => 1,
                       'filter' => $form_date->getTimestamp()
                    ));
                }
            }

            $date = new \DateTime();
            if($filter > 1){
                $date->setTimestamp($filter);
            }
            $date->setTime(0,0,0);

            $permission = $this->getDoctrine()->getRepository(Seanse::class)->checkSeancesForMovie($movie);

            $pageLimit = 5;
            $seancesPageCount = $this->getDoctrine()->getRepository(Seanse::class)
                ->getPageCountForMovie($movie, $date, $pageLimit);

            if($seancesPageCount > 0) {
                if($seancesPageCount < $page) $page=1;
                $seancesPage = $this->getDoctrine()->getRepository(Seanse::class)
                    ->findSeancesForMovie($movie, $date, $page, $pageLimit);
            } else {
                $seancesPage = NULL;
            }

            return $this->render('workersApp/movies/show.html.twig', array(
                'movie' => $movie,
                'dateInput' => $date,
                'pageCount' => $seancesPageCount,
                'seancesPage' => $seancesPage,
                'currentPage' => $page,
                'permission' => $permission,
                'pathName' => 'workers_app/movies/show',
                'pathParams' => array(
                    'id' => $id,
                    'filter' => $filter
                )
            ));
        } else {
            return $this->redirectToRoute('workers_app/login_page');
        }
    }

    /**
     * @Route("/movie/delete/{id<[1-9]\d*>?}", name="workers_app/movies/delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        if (AppController::logoutOnSessionLifetimeEnd($this->get('session'))) {
            return $this->redirectToRoute('workers_app/logout_page');
        }
        if($this->isGranted('ROLE_MANAGER') or $this->isGranted('ROLE_ADMIN')) {
            $movie = $this->getDoctrine()->getRepository(Filmy::class)->find($id);
            if($movie != null and $this->getDoctrine()->getRepository(Seanse::class)->checkSeancesForMovie($movie)) {
                if($movie->getPlakat() and file_exists('../../images/' . $movie->getPlakat()))
                    unlink('../../images/' . $movie->getPlakat());

                $entityManager = $this->getDoctrine()->getManager();

                $entityManager->remove($movie);
                $entityManager->flush();
            }
        }
    }

    /**
     * @param Filmy $movie
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getForm(Filmy $movie)
    {
        return $this->createFormBuilder($movie)
            ->add('tytul', TextType::class, array(
                'label' => 'Tytuł:',
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Wprowadź tytuł filmu...',
                    'autocomplete' => 'off',
                    'pattern' => '.{2,127}',
                    'title' => 'Od 2 do 127 znaków'
                )
            ))
            ->add('plakat', FileType::class, array(
                'label' => 'Plakat:',
                'required' => false,
                'multiple' => false,
                'attr' => array(
                    'accept' => 'image/*',
                    'onchange' => 'readURL(this);',
                    'class' => 'form-control-file bg-light text-dark',
                    'title' => 'Obraz o rozmiarze do 1000KB'
                )
            ))
            ->add('datapremiery', DateType::class, array(
                'label' => 'Data premiery',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => array(
                    'class' => 'form-control col-xl-6 col-md-10 col-10',
                    'autocomplete' => 'off',
                    'title' => 'Data'
                ),
            ))
            ->add('kategoriewiekowe', EntityType::class, array(
                'class' => Kategoriewiekowe::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'label' => 'Kategoria wiekowa:',
                'expanded' => false,
                'multiple' => false,
                'attr' => array(
                    'class' => 'form-control col-xl-4 col-md-6 col-8'
                )
            ))
            ->add('czastrwania', IntegerType::class, array(
                'label' => 'Długość filmu:',
                'invalid_message' => 'Wartość musi być liczbą całkowitą',
                'attr' => array(
                    'class' => 'form-control col-xl-4 col-md-6 col-8',
                    'autocomplete' => 'off',
                    'min' => '1',
                    'max' => '720',
                    'step' => '1',
                    'title' => 'Od 1 do 720'
                )
            ))
            ->add('czasreklam', IntegerType::class, array(
                'label' => 'Długość reklam:',
                'invalid_message' => 'Wartość musi być liczbą całkowitą',
                'attr' => array(
                    'class' => 'form-control col-xl-4 col-md-6 col-8',
                    'autocomplete' => 'off',
                    'min' => '0',
                    'max' => '30',
                    'step' => '1',
                    'title' => 'Od 0 do 30'
                )
            ))
            ->add('typyseansow', EntityType::class, array(
                'class' => Typyseansow::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'label' => 'Dostępne formaty:',
                'expanded' => true,
                'multiple' => true,
                'attr' => array(
                    'class' => 'checkboxes-group'
                )
            ))
            ->add('rodzajefilmow', EntityType::class, array(
                'class' => Rodzajefilmow::class,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->andWhere('d.usunieto = 0 OR d.usunieto IS NULL');
                },
                'required' => true,
                'label' => 'Gatunki:',
                'expanded' => true,
                'multiple' => true,
                'attr' => array(
                    'class' => 'checkboxes-group'
                )
            ))
            ->add('opis', TextareaType::class, array(
                'label' => 'Opis:',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Wprowadź opis filmu...',
                    'maxlength' => '512',
                    'title' => 'Maksymalnie 512 znaków'
                )
            ))
            ->add('zwiastun', UrlType::class, array(
                'label' => 'Zwiastun:',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Wklej link do zwiastuna...',
                    'title' => 'Pełny adres URL',
                    'autocomplete' => 'off'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Zapisz',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
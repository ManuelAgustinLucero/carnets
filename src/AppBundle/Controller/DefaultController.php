<?php

namespace AppBundle\Controller;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Pedido;
use AppBundle\Entity\Socio;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Date;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
//        return $this->render('default/index.html.twig', [
//            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
//        ]);
        if ($this->getUser()){
            if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
                return $this->redirectToRoute('admin_pedido_index');
            }else{
                $em = $this->getDoctrine()->getManager();

                $pedidos = $em->getRepository('AppBundle:Pedido')->findBy( ['user' =>$this->getUser()->getId() ]);

                return $this->render('userPedido/index.html.twig', array(
                    'pedidos' => $pedidos,
                ));

            }
        }else{
            return $this->redirectToRoute('fos_user_security_login');

        }

    }

    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/user/{id}", name="user_pedido_show")
     * @Method("GET")
     */
    public function showAction(Pedido $pedido=null)
    {
            $em = $this->getDoctrine()->getManager();
            $socios= $em->getRepository('AppBundle:Socio')->findBy( ['pedido' =>$pedido->getId() ]);

            return $this->render('userPedido/show.html.twig', array(
                'pedido' => $pedido,
                'socios'=>$socios,
            ));


    }

    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/buscar/socio", name="socio")
     * @Method("GET")
     */
    public function SocioAction()
    {

        return $this->render('socio/socio.html.twig', array(

        ));


    }

    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/sociosede", name="sociosede")
     * @Method("GET")
     */
    public function SocioSedeAction()
    {

        return $this->render('buscarsocio/sociobuscar.html.twig', array(

        ));


    }
    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/searh", name="searh_socio")
     * @Method({"GET", "POST"})
     */
    public function searhsocioAction(Request $request)
    {
        $nombre= $request->request->get('socio');

        if ($nombre !=''){
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("AppBundle:User");
            $query = $repository->createQueryBuilder('u')
                ->select(array(
                        'u.id',
                        'u.username as sede',
                        'p.codigo as pedidocodigo',
                        's.nombreApellido',
                        's.codigo as codigoSocio'

                    )
                )
                ->innerJoin('AppBundle:Pedido', 'p', 'WITH', 'p.user = u.id')
                ->innerJoin('AppBundle:Socio', 's', 'WITH', 'p.id = s.pedido')

                ->where('s.nombreApellido LIKE :nombre')
                ->setParameter('nombre', $nombre.'%')

                ->setMaxResults(10000)
            ;
            $socios=$query->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        }else{
            $socios=false;
        }



        return new JsonResponse($socios);

    }
    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/searhsociosede", name="searh_socio_sede")
     * @Method({"GET", "POST"})
     */
    public function searhsocioYSedeAction(Request $request)
    {
        $nombre= $request->request->get('socio');
        $codigo=$this->getUser()->getCodigo();
        if ($nombre !=''){
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository("AppBundle:User");
            $query = $repository->createQueryBuilder('u')
                ->select(array(
                        'u.id',
                        'u.username as sede',
                        'p.codigo as pedidocodigo',
                        's.nombreApellido',
                        's.codigo as codigoSocio'

                    )
                )
                ->innerJoin('AppBundle:Pedido', 'p', 'WITH', 'p.user = u.id')
                ->innerJoin('AppBundle:Socio', 's', 'WITH', 'p.id = s.pedido')

                ->where('s.nombreApellido LIKE :nombre')
                ->andWhere('u.codigo = :codigo')
                ->setParameter('nombre', $nombre.'%')
                ->setParameter('codigo', $codigo)

                ->setMaxResults(10000)
            ;
            $socios=$query->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        }else{
            $socios=false;
        }



        return new JsonResponse($socios);

    }
    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/users/{id}/edit", name="editProfile")
     * @Method({"GET", "POST"})
     */
    public function editProfileAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($id);

        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $user;

            $userManager->updateUser($user);

            $session = $this->getRequest()->getSession();
            $session->getFlashBag()->add('message', 'Successfully updated');
            return $this->redirectToRoute('admin_sede_index');


        }

        return $this->render('FOSUserBundle:Profile:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/users/changePass", name="changePass")
     * @Method({"GET", "POST"})
     */
    public function changePassAction(Request $request,UserPasswordEncoderInterface $encoder)
    {
        $id= $request->request->get('id');
        $username= $request->request->get('username');
        $email= $request->request->get('email');
        $codigo= $request->request->get('codigo');
        $password= $request->request->get('password');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($id);

//        if ($email){
//            $email = $em->getRepository('AppBundle:User')->findOneByEmail($email);
//            if ($email){
//                return new JsonResponse("Ya existe un email ".$email);
//
//            }
//        }

        if ($password){
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setCodigo($codigo);
            $encoded = $encoder->encodePassword($user, $password);
            $user->setPassword($encoded);
            $em->persist($user);
            $em->flush();
        }
        else{
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setCodigo($codigo);
            $em->persist($user);
            $em->flush();
        }

        return new JsonResponse("Cambios actualizados con exito");

    }

}

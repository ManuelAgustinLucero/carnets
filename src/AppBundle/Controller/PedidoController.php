<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Pedido;
use AppBundle\Entity\Socio;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Pedido controller.
 *
 * @Route("admin/pedido")
 */
class PedidoController extends Controller
{
    /**
     * Lists all pedido entities.
     *
     * @Route("/", name="admin_pedido_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $pedidos = $em->getRepository('AppBundle:Pedido')->findAll();

        return $this->render('pedido/index.html.twig', array(
            'pedidos' => $pedidos,
        ));
    }

    /**
     * Creates a new pedido entity.
     *
     * @Route("/new", name="admin_pedido_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $pedido = new Pedido();
        $form = $this->createForm('AppBundle\Form\PedidoType', $pedido);
        $form->get('fechaRecepcion')->setData( new \DateTime('now'));
        $form->get('fechaEntrega')->setData( null);
        $form->get('codigo')->setData( 'a');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $file=$form['listaSocios']->getData();
            // Sacamos la extensión del fichero
            $ext=$file->guessExtension();
            // Le ponemos un nombre al fichero
            $file_name='file'.".".$ext;
            // Guardamos el fichero en el directorio uploads que estará en el directorio /web del framework
            $file->move("uploads", $file_name);

             //Establecemos el nombre de fichero en el atributo de la entidad
            $pedido->setListaSocios($file_name);
//            $em->persist($pedido);
//            $em->flush();
            $path = "uploads/".$file_name;
            if (!file_exists($path))
                exit("File not found");
            $file = fopen($path, "r");
            $block=0;
            $blockearch=0;
            if ($file) {

                while (($line = fgets($file)) !== false) {
                    if ( !mb_check_encoding($line, 'UTF-8')) {
                        $this->addFlash(
                            'notice',
                            'EL ARCHIVO NO ESTA EN FORMATO UFT8!'

                        );
                        fclose($file);

                        return $this->render('pedido/new.html.twig', array(
                            'pedido' => $pedido,
                            'form' => $form->createView(),
                        ));

                    }
                    $resulta = explode(";", $line);
                    if ($blockearch!=0){
                        $sede= $em->getRepository('AppBundle:User')->findOneByCodigo($resulta[2]);
                        if ( !$sede){
                            $this->addFlash(
                                'notice',
                                '¡La sede con codigo '.$resulta[2].' no existe!'

                            );
                            fclose($file);

                            return $this->render('pedido/new.html.twig', array(
                                'pedido' => $pedido,
                                'form' => $form->createView(),
                            ));
                        }
                    }
                    $blockearch=1;


                }
                if (!feof($file)) {
                    echo "Error: EOF not found\n";
                }
                fclose($file);

                $file = fopen($path, "r");

                while (($line = fgets($file)) !== false) {
                    $result = explode(";", $line);

                    if ($block !=0 ){
                        $sede= $em->getRepository('AppBundle:User')->findOneByCodigo($resulta[2]);
                        $checkPedido= $em->getRepository('AppBundle:Pedido')->findOneBy(array('codigo' => $result[4], 'user' => $sede->getId()));

                        if ($checkPedido){
                            $id_pedido=  $checkPedido;

                        }
                        else{
                            //CONVERTIMOS LA FECHA
                            $data= explode(" ",$result[7]);
                            $date= explode("/", $data[0]);
                            $test=$date[2]."-".$date[1]."-".$date[0];
                            $fechaHasta =  date('Y-m-d H:i:s' , strtotime("$test.T22:00:00.000Z")); /* « notice the trim */
                            //FIN DE CONVERTIR LA FECHA
                            $sede= $em->getRepository('AppBundle:User')->findOneByCodigo($result[2]);
                            $pedidolista = new Pedido();
                            $pedidolista->setCodigo($result[4]);
                            $pedidolista->setListaSocios('lista');
                            $pedidolista->setUser($sede);
                            $pedidolista->setFechaRecepcion(new \DateTime(strval($fechaHasta)));
                            $em->persist($pedidolista);
                            $em->flush();
                            $id_pedido= $em->getRepository('AppBundle:Pedido')->find( $pedidolista->getId());



                        }
                            $socio= new Socio();
                            $socio->setCodigo(strval($result[0]));
                            $socio->setNombreApellido($result[1]);
                            $socio->setPedido($id_pedido);
                            $em->persist($socio);
                            $em->flush();


                    }
                    $block=1;

                }
                if (!feof($file)) {
                    echo "Error: EOF not found\n";
                }
                fclose($file);

            }
            //mailer sede

//            $message = \Swift_Message::newInstance()
//                ->setSubject('Poda')
//                ->setFrom('carnet@poda.com.ar')
//                ->setTo($pedido->getUser()->getEmail())
//                ->setBody('Los pedido han sido  creado')
//            ;
//
//            $this->get('mailer')->send($message);
//
//            //mailer administrador
//            $em->persist($pedido);
//            $em->flush();
//            $message = \Swift_Message::newInstance()
//                ->setSubject('Poda')
//                ->setFrom('carnet@poda.com.ar')
//                ->setTo($user = $this->getUser()->getEmail())
//                ->setBody('El pedido '.$pedido->getCodigo().'ha sido  creado')
//            ;
//
//            $this->get('mailer')->send($message);
            return $this->redirectToRoute('admin_pedido_index');
        }
        return $this->render('pedido/new.html.twig', array(
            'pedido' => $pedido,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/{id}", name="admin_pedido_show")
     * @Method("GET")
     */
    public function showAction(Pedido $pedido)
    {
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($pedido);
        $socios= $em->getRepository('AppBundle:Socio')->findBy( ['pedido' =>$pedido->getId() ]);

        return $this->render('pedido/show.html.twig', array(
            'pedido' => $pedido,
            'socios'=>$socios,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing pedido entity.
     *
     * @Route("/{id}/edit", name="admin_pedido_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Pedido $pedido)
    {
        $deleteForm = $this->createDeleteForm($pedido);
        $editForm = $this->createForm('AppBundle\Form\PedidoType', $pedido);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_pedido_edit', array('id' => $pedido->getId()));
        }

        return $this->render('pedido/edit.html.twig', array(
            'pedido' => $pedido,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a pedido entity.
     *
     * @Route("/{id}", name="admin_pedido_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Pedido $pedido)
    {
        $form = $this->createDeleteForm($pedido);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $socios= $em->getRepository('AppBundle:Socio')->findBy( ['pedido' =>$pedido->getId() ]);
            foreach ($socios as $valor) {
                $em->remove($valor);
                $em->flush();            }
            $em->remove($pedido);
            $em->flush();
        }

        return $this->redirectToRoute('admin_pedido_index');
    }

    /**
     * Creates a form to delete a pedido entity.
     *
     * @param Pedido $pedido The pedido entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Pedido $pedido)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_pedido_delete', array('id' => $pedido->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Finds and displays a pedido entity.
     *
     * @Route("/entrega", name="admin_pedido_entrega")
     * @Method({"GET", "POST"})
     */
    public function entregaAction(Request $request)
    {
        $id= $request->request->get('id');
        $fecha= $request->request->get('fecha');
        $check= $request->request->get('check');

        $em = $this->getDoctrine()->getManager();

        $pedido= $em->getRepository('AppBundle:Pedido')->find($id);
        $pedido->setFechaEntrega(new \DateTime($fecha));
        $sede=$pedido->getUser(new \DateTime($fecha));
        if ($check == "activado"){
            //mailer sede
            $em->persist($pedido);
            $em->flush();
            $message = \Swift_Message::newInstance()
                ->setSubject('Entrega de carnets')
                ->setFrom('carnets@poda.com.ar',"Carnets Poda")
                ->setTo($pedido->getUser()->getEmail())
                ->setBody('Su  pedido '.$pedido->getCodigo().' ha sido entregado el día '.$fecha.
                    '. No responder a esta direccion de mail, responder a carnets@sportclub.com.ar')

            ;

            $this->get('mailer')->send($message);

            //mailer administrador

            $message = \Swift_Message::newInstance()
                ->setSubject('Entrega de carnets')
                ->setFrom('carnets@poda.com.ar',"Carnets Poda")
                ->setTo($user = $this->getUser()->getEmail())
                ->setBody('El pedido '.$pedido->getCodigo().' ha sido entregado el día '.$fecha.
                    '. No responder a esta direccion de mail, responder a carnets@sportclub.com.ar' )

            ;

            $this->get('mailer')->send($message);
            $mensaje="El pedido a ". "<b>".$sede. "</b>"." fue entregado con exito y enviado el mail";
            return new JsonResponse($mensaje);

        }else{
            $em->persist($pedido);
            $em->flush();
            $mensaje="El pedido a ". "<b>".$sede. "</b>"." fue entregado con exito ";
            return new JsonResponse($mensaje);
        }




    }
}

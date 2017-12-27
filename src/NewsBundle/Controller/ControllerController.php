<?php

namespace NewsBundle\Controller;

use NewsBundle\Entity\Comments;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use NewsBundle\Entity\News;


class ControllerController extends Controller
{
    /**
     * @Route("/", name = "homepage")
     * @Template("/Users/virtua/Sites/sf2_pro2/sf2_pro2/symfony2Pro/app/Resources/views/main.html.twig");
     */
    public function mainAction()
    {
        $name = 'News Page';

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:News');

        //sortowanie od najnowszego
        $query = $repo->createQueryBuilder('p')
            ->addOrderBy("p.id", 'DESC')
            ->getQuery();

        $rows = $query->getResult();

        ////// pobieranie ilosci komentarzy


        $tablica = [];
        foreach ($rows as $item)
        {
            array_push($tablica, $item->getId());
        }
        $counter = count($tablica);

            for($i = 0; $i <= $counter-1; $i++)
            {
                $em = $this->getDoctrine()->getManager();
                $repo = $em->getRepository('NewsBundle:Comments');
                $allNews = $repo->findBy(
                    array('titleID' => $tablica[$i]));

                $em = $this->getDoctrine()->getManager();
                $repo = $em->getRepository('NewsBundle:News')->find($tablica[$i]);

                $repo->setCountOfComments(count($allNews));

                $em->persist($repo);
                $em->flush();
            }

        return array ('name' => $name,
            'rows' => $rows);
    }

    /**
     * @Route("/admin", name = "admin")
     * @Template("/Users/virtua/Sites/sf2_pro2/sf2_pro2/symfony2Pro/app/Resources/views/admin.html.twig");
     */
    public function adminAction()
    {
        $admin = 'strona admina';

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:News');

        $query = $repo->createQueryBuilder('p')
            ->addOrderBy("p.id", 'DESC')
            ->getQuery();
        $rows = $query->getResult();


        $tablica = [];
        foreach ($rows as $item)
        {
            array_push($tablica, $item->getId());
        }
        $counter = count($tablica);

        for($i = 0; $i <= $counter-1; $i++)
        {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('NewsBundle:Comments');
            $allNews = $repo->findBy(
                array('titleID' => $tablica[$i]));

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('NewsBundle:News')->find($tablica[$i]);

            $repo->setCountOfComments(count($allNews));

            $em->persist($repo);
            $em->flush();
        }

        return array('admin' => $admin,
            'name' => 'strona admina',
            'rows' => $rows,
            'delete' => 'delete');
    }

    /**
     * @Route("/admin/createNews", name = "createNews")
     */
    public function createNewsAction(Request $request)
    {

        $nick = $request->request->get('nick');
        $title = $request->request->get('title');
        $newsContent = $request->request->get('news');

        $news = new News();
        $news->setNick($nick);
        $news->setTitle($title);
        $news->setNews($newsContent);

        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();

        return $this->redirectToRoute('homepage', array('rows' => $news));

    }

    /**
     * @Route("/admin/deleteNews/{id}", name = "deleteNews")
     */
    public function deleteAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:News')->find($id);

        $em->remove($repo);
        $em->flush();

        return $this->redirectToRoute('admin');

    }

    /**
     * @Route("/comment/add/{id}", name = "addComment")
     * @Template("/Users/virtua/Sites/sf2_pro2/sf2_pro2/symfony2Pro/app/Resources/views/comment.html.twig")
     */
    public function commentAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:News')->find($id);

        return array('rowsComment' => $repo);

    }

    /**
     * @Route("/comment/pushComment/{id}", name = "pushComment")
     * @Template("/Users/virtua/Sites/sf2_pro2/sf2_pro2/symfony2Pro/app/Resources/views/showComment.html.twig")
     */
    public function addCommentAction($id, Request $request)
    {
        $idNewsa = $id;
        $nickCom = $request->request->get('nickCom');
        $commentContent = $request->request->get('comment');

        $comment = new Comments();
        $comment->setNick($nickCom);
        $comment->setTitleID($idNewsa);
        $comment->setComment($commentContent);

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('showerComment', array('id' => $idNewsa));

    }

    /**
     * @Route("/comment/show/{id}", name = "showerComment")
     * @Template("/Users/virtua/Sites/sf2_pro2/sf2_pro2/symfony2Pro/app/Resources/views/comment2.html.twig")
     */
    public function showerCommentAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('NewsBundle:News')->find($id);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:Comments');
        $allNews = $repo->findBy(
            array('titleID' => $id)
        );

        // sortowanie od najnowszego

        $commentsArrayFree = [];
        foreach ($allNews as $item)
        {
            array_push($commentsArrayFree, $item);
        }

        $counter = count($commentsArrayFree);
        $counter = $counter-1;
        $commentsArrayFinal = [];

        for ($i = 0; $i <= $counter; $i++)
        {
            array_push($commentsArrayFinal,$commentsArrayFree[$counter-$i]);
        }

        $rows = $commentsArrayFinal;
        $count = count($rows);

        return array('rowsCom' => $rows,
                    'rowsComment' => $rep,
                    'count' => $count);
    }

    /**
     * @Route("/admin/comment/show/{id}", name = "showerCommentAdmin")
     * @Template("/Users/virtua/Sites/sf2_pro2/sf2_pro2/symfony2Pro/app/Resources/views/comment2.html.twig")
     */
    public function showerCommentAdminAction($id)
    {
        $newsPage = $id;
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('NewsBundle:News')->find($id);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:Comments');
        $allNews = $repo->findBy(array(
            'titleID' => $id
        ));

        $commentsArrayFree = [];
        foreach ($allNews as $item)
        {
            array_push($commentsArrayFree, $item);
        }

        $counter = count($commentsArrayFree);
        $counter = $counter-1;
        $commentsArrayFinal = [];

        for ($i = 0; $i <= $counter; $i++)
        {
            array_push($commentsArrayFinal,$commentsArrayFree[$counter-$i]);
        }

        $rows = $commentsArrayFinal;

        $count = count($rows);

        return array('rowsCom' => $rows,
            'rowsComment' => $rep,
            'delete' => TRUE,
            'newsPage' => $newsPage,
            'count' => $count);
    }

    /**
     * @Route("/admin/deleteComment/{id}/{newsPage}", name = "deleteComment")
     */
    public function deleteCommentAction($id, $newsPage)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:Comments')->find($id);

        $em->remove($repo);
        $em->flush();

        return $this->redirect('/admin/comment/show/'.$newsPage);

    }

    /**
     * @Route("/admin/banComment/{id}/{newsPage}", name = "banComment")
     */
    public function banCommentAction($id, $newsPage)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NewsBundle:Comments')->find($id);

        $tekst = "### KOMENTARZ ZOSTAŁ USUNIETY PRZEZ ADMINISTRATORA ###";
        $repo->setComment($tekst);

        $em->persist($repo);
        $em->flush();

        return $this->redirect('/admin/comment/show/'.$newsPage);

    }


}

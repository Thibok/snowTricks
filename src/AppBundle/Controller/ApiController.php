<?php

/**
 * Api controller
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * ApiController
 */
class ApiController extends Controller
{
    /**
     * Get comments with Ajax
     * @access public
     * @param Request $request
     * @param int $trickId
     * @param int $nbComments
     * @Route(
     *     "/api/comments/{trickId}/{nbComments}",
     *     name="st_api_comments",
     *     requirements={"trickId"="\d+", "nbComments"="\d+"}
     * )
     * 
     * @return void
     */
    public function getCommentsAction(Request $request, $trickId, $nbComments)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $page = ceil($nbComments / Comment::COMMENT_PER_PAGE + 1);
        $comments = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Comment::class)
            ->getComments($trickId, $page, Comment::COMMENT_PER_PAGE);

        $datas = [];

        foreach ($comments as $comment) {
            $user = $comment->getUser();

            if ($this->getParameter('kernel.environment') == 'test') {
                $imgSrc = $user->getImage()->getUploadWebTestThumbPath();
            } else {
                $imgSrc = $user->getImage()->getUploadWebThumbPath();
            }
            
            $data = [
                'imgSrc' => $imgSrc,
                'author' => $user->getFirstName() .' '. $user->getName(),
                'content' => $comment->getContent(),
                'date' => $comment->getAddAt()->format('d-m-Y \a\t H\hi\m\i\n s\s')
            ];

            $datas[] = $data;
        }

        return new JsonResponse($datas);
    }
}
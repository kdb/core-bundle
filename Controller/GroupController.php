<?php
/**
 * @file
 * Contains the group controller.
 */

namespace Os2Display\CoreBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Util\Codes;
use Os2Display\CoreBundle\Entity\Group;
use Os2Display\CoreBundle\Exception\DuplicateEntityException;
use Os2Display\CoreBundle\Exception\HttpDataException;
use Os2Display\CoreBundle\Exception\ValidationException;
use Os2Display\CoreBundle\Security\GroupRoles;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Group controller.
 *
 * @Route("api/group")
 * @Rest\View(serializerGroups={"api"})
 */
class GroupController extends ApiController
{
    protected static $editableProperties = ['title'];

    /**
     * Lists all group entities.
     *
     * @Rest\Get("", name="api_group_index")
     * @ApiDoc(
     *   section="Groups",
     *   description="Get all groups",
     *   tags={"group"}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function indexAction()
    {
        $groups = $this->findAll(Group::class);
        return $this->setApiData($groups);
    }

    /**
     * Creates a new group entity.
     *
     * @Rest\Post("", name="api_group_new")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $data = $this->getData($request);

        try {
            $group = $this->get('os2display.group_manager')->createGroup($data);
        } catch (ValidationException $e) {
            throw new HttpDataException(
                Codes::HTTP_BAD_REQUEST,
                $data,
                'Invalid data',
                $e
            );
        } catch (DuplicateEntityException $e) {
            throw new HttpDataException(
                Codes::HTTP_CONFLICT,
                $data,
                'Duplicate group',
                $e
            );
        }

        // Send response.
        return $this->createCreatedResponse($this->setApiData($group));
    }

    /**
     * @Rest\Get("/roles")
     * @ApiDoc(
     *   section="Groups",
     *   description="Get all available group roles"
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    public function getRolesAction(Request $request)
    {
        $translator = $this->get('translator');
        $locale = $request->get('locale', $this->getParameter('locale'));

        $roles = GroupRoles::getRoleNames();
        $labels = array_map(function ($role) use ($translator, $locale) {
            return $translator->trans(
                $role,
                [],
                'Os2DisplayCoreBundle',
                $locale
            );
        }, $roles);
        $data = array_combine($roles, $labels);
        asort($data);

        return $data;
    }

    /**
     * Finds and displays a group entity.
     *
     * @Rest\Get("/{id}", name="api_group_show")
     *
     * @Security("is_granted('READ', group)")
     *
     * @param \Os2Display\CoreBundle\Entity\Group $group
     * @return Group
     */
    public function showAction(Group $group)
    {
        $group->buildUsers();

        return $this->setApiData($group);
    }

    /**
     * Displays a form to edit an existing group entity.
     *
     * @Rest\Put("/{id}", name="api_group_edit")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Os2Display\CoreBundle\Entity\Group $group
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function editAction(Request $request, Group $group)
    {
        $data = $this->getData($request);

        try {
            $group = $this->get('os2display.group_manager')
                ->updateGroup($group, $data);
        } catch (ValidationException $e) {
            throw new HttpDataException(
                Codes::HTTP_BAD_REQUEST,
                $data,
                'Invalid data',
                $e
            );
        } catch (DuplicateEntityException $e) {
            throw new HttpDataException(
                Codes::HTTP_CONFLICT,
                $data,
                'Duplicate group',
                $e
            );
        }

        return $this->setApiData($group);
    }

    /**
     * Deletes a group entity.
     *
     * @Rest\Delete("/{id}", name="api_group_delete")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Os2Display\CoreBundle\Entity\Group $group
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, Group $group)
    {
        $em = $this->getDoctrine()->getManager();

        $userGroups = $em->getRepository('Os2DisplayCoreBundle:UserGroup')->findBy(['group' => $group->getId()]);

        foreach ($userGroups as $userGroup) {
            $em->remove($userGroup);
        }

        $groupings = $em->getRepository('Os2DisplayCoreBundle:Grouping')->findBy(['group' => $group->getId()]);

        foreach ($groupings as $grouping) {
            $em->remove($grouping);
        }

        $em->remove($group);
        $em->flush();

        return $this->view(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Get users with roles in group.
     *
     * @Rest\Get("/{group}/users")
     */
    public function getGroupUsersAction(Group $group)
    {
        $users = $group->buildUsers()->getUsers();

        foreach ($users as $user) {
            $user->buildGroupRoles($group);
        }

        return $this->setApiData($users);
    }

}

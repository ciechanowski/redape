<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\PasswordChangeType;
use App\Form\ProfileDataType;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Service\ClientManager;
use App\Service\FlashManager;
use App\Service\RecipeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile/data", name="app_profile_data")
     */
    public function profileData(Request $request, ClientManager $clientManager)
    {
        $form = $this->createForm(ProfileDataType::class, $clientManager->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clientManager->saveUser($form->getData());

            $this->addFlash(FlashManager::FLASH_TYPE_SUCCESS, FlashManager::FLASH_MESSAGE_FORM_DATA_SAVED);
        }

        return $this->render('profile/data.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/change-password", name="app_profile_password")
     */
    public function profilePassword(Request $request, ClientManager $clientManager)
    {
        $form = $this->createForm(PasswordChangeType::class, $clientManager->getUser());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clientManager->saveUserPassword($form->getData());

            $this->addFlash(FlashManager::FLASH_TYPE_SUCCESS, FlashManager::FLASH_MESSAGE_FORM_DATA_SAVED);
        }

        return $this->render('profile/passwordChange.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/recipes/list", name="app_profile_recipe_list")
     */
    public function profileRecipeList(RecipeRepository $recipeRepository, ClientManager $clientManager)
    {
        $recipes = $recipeRepository->findBy(['user' => $clientManager->getUser()]); //@TODO pagination and use SearchManager for optimization

        return $this->render('profile/recipeList.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * @Route("/profile/recipes/create", name="app_profile_recipe_create")
     */
    public function profileRecipeCreate()
    {
        $form = $this->createForm(RecipeType::class, new Recipe(), ['action' => $this->generateUrl('app_recipe_persist')]);

        return $this->render('profile/recipeCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/recipes/edit/{id}", name="app_profile_recipe_edit", requirements={"id"="\d+"})
     */
    public function profileRecipeEdit(int $id, RecipeRepository $recipeRepository, ClientManager $clientManager)
    {
        $recipe = $recipeRepository->findOneBy(['user' => $clientManager->getUser(), 'id' => $id]);

        if (!$recipe) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(RecipeType::class, $recipe, ['action' => $this->generateUrl('app_recipe_persist')]);

        return $this->render('profile/recipeEdit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/recipe-persist", name="app_recipe_persist", methods={"POST"})
     */
    public function recipePersist(Request $request, RecipeManager $recipeManager)
    {
        $form = $this->createForm(RecipeType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipeManager->saveRecipe($form->getData());

            $this->addFlash(FlashManager::FLASH_TYPE_SUCCESS, FlashManager::FLASH_MESSAGE_FORM_DATA_SAVED);
        }

//@TODO redirect, errors returning
        return $this->json(['@TODO']);
    }

}

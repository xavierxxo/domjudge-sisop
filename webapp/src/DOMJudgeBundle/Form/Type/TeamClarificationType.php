<?php declare(strict_types=1);

namespace DOMJudgeBundle\Form\Type;

use DOMJudgeBundle\Entity\ContestProblem;
use DOMJudgeBundle\Service\DOMJudgeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class TeamClarificationType extends AbstractType
{
    /**
     * @var DOMJudgeService
     */
    protected $DOMJudgeService;

    public function __construct(DOMJudgeService $DOMJudgeService)
    {
        $this->DOMJudgeService = $DOMJudgeService;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('recipient', ChoiceType::class, [
            'choices' => ['Jury' => 'dummy'],
            'attr' => [
                'readonly' => true,
            ],
        ]);

        $subjects = [];
        /** @var string[] $categories */
        $categories = $this->DOMJudgeService->dbconfig_get('clar_categories');
        $user       = $this->DOMJudgeService->getUser();
        $contest    = $this->DOMJudgeService->getCurrentContest($user->getTeamid());
        foreach ($categories as $categoryId => $categoryName) {
            $subjects[$categoryName] = sprintf('%d-%s', $contest->getCid(), $categoryId);
        }
        if ($contest->getFreezeData()->started()) {
            /** @var ContestProblem $problem */
            foreach ($contest->getProblems() as $problem) {
                if ($problem->getAllowSubmit()) {
                    $problemName            = sprintf('%s: %s', $problem->getShortname(),
                                                      $problem->getProblem()->getName());
                    $subjects[$problemName] = sprintf('%d-%d', $contest->getCid(), $problem->getProbid());
                }
            }
        }

        $builder->add('subject', ChoiceType::class, [
            'choices' => $subjects,
        ]);
        $builder->add('message', TextareaType::class, [
            'attr' => [
                'rows' => 5,
                'cols' => 85,
            ],
        ]);
    }
}

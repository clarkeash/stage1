<?php

namespace App\CoreBundle\Command;

use App\Model\Project;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectFixProviderCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('stage1:project:fix-provider');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $repository = $doctrine->getRepository('Model:Project');
        $manager = $doctrine->getManager();

        foreach ($repository->findAll() as $project) {
            try {
                if ($this->fixProvider($output, $project)) {
                    $manager->persist($project);
                }

                if ($project->getProviderName() === 'github') {
                    if (null === $project->getIsPrivate()) {
                        $output->writeln('fixing private status for <info>'.$project->getFullName().'</info>');
                        $project->setIsPrivate($project->getProviderData('private'));

                        $manager->persist($project);
                    }

                    if (strlen($project->getGitUrl()) === 0) {
                        $output->writeln('fixing git_url for <info>'.$project->getFullName().'</info>');
                        $field = $project->getIsPrivate() ? 'ssh_url' : 'clone_url';
                        $project->setGitUrl($project->getProviderData($field));

                        $manager->persist($project);
                    }
                }

            } catch (\Exception $e) {
                $output->writeln('<error>could not fix provider for project "'.$project->getGithubFullName().'</error>');
                $output->writeln('<error>'.$e->getMessage().'</error>');
            }
        }

        $manager->flush();
    }

    private function fixProvider(OutputInterface $output, Project $project)
    {
        if (strlen($project->getProviderName()) > 0) {
            $output->writeln('project <info>'.$project->getFullName().'</info> already migrated');
            return false;
        }

        $output->writeln('migrating project <info>'.$project->getFullName().'</info>');

        $project->setFullName($project->getGithubFullName());

        $project->setProviderName('github');
        $project->setProviderData([
            'clone_url' => $project->getCloneUrl(),
            'ssh_url' => $project->getSshUrl(),
            'keys_url' => $project->getKeysUrl(),
            'hooks_url' => $project->getHooksUrl(),
            'contents_url' => $project->getContentsUrl(),
            'id' => $project->getGithubId(),
            'full_name' => $project->getGithubFullName(),
            'owner_login' => $project->getGithubOwnerLogin(),
            'hook_id' => $project->getGithubHookId(),
            'deploy_key_id' => $project->getGithubDeployKeyId(),
            'private' => $project->getGithubPrivate(),
            'url' => $project->getGithubUrl(),
        ]);

        return true;
    }
}
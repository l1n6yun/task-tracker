#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

const CONFIG_JSON_FILE = __DIR__ . '/config.json';
function getConfig()
{
    return !file_exists(CONFIG_JSON_FILE) ? [
        'AUTO_INCREMENT' => 1,
        'TASK' => [],
    ] : json_decode(file_get_contents(CONFIG_JSON_FILE), true);
}

function setConfig($tasks): void
{
    file_put_contents(CONFIG_JSON_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
}

$application = new Application();

$application->register('add')
    ->setDescription('Add a new task')
    ->addArgument('description', InputArgument::REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $description = $input->getArgument('description');

        $config = getConfig();

        $taskId = $config['AUTO_INCREMENT'];
        $config['TASK'][$config['AUTO_INCREMENT']] = [
            'id' => $config['AUTO_INCREMENT'],
            'description' => $description,
            'status' => 'todo',
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s'),
        ];
        $config['AUTO_INCREMENT']++;

        setConfig($config);

        $output->writeln('<info>Task added successfully (ID: ' . $taskId . ')</info>');
        return Command::SUCCESS;
    });

$application->register('update')
    ->setDescription('Update a task')
    ->addArgument('id', InputArgument::REQUIRED)
    ->addArgument('description', InputArgument::REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $id = $input->getArgument('id');
        $description = $input->getArgument('description');

        $config = getConfig();

        if (!isset($config['TASK'][$id])) {
            $output->writeln('<error>Task not found</error>');
            return Command::FAILURE;
        }

        $config['TASK'][$id]['description'] = $description;
        $config['TASK'][$id]['updatedAt'] = date('Y-m-d H:i:s');

        setConfig($config);

        $output->writeln('<info>Task updated successfully</info>');
        return Command::SUCCESS;
    });

$application->register('delete')
    ->setDescription('Delete a task')
    ->addArgument('id', InputArgument::REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $id = $input->getArgument('id');

        $config = getConfig();

        if (!isset($config['TASK'][$id])) {
            $output->writeln('<error>Task not found</error>');
            return Command::FAILURE;
        }

        unset($config['TASK'][$id]);

        setConfig($config);

        $output->writeln('<info>Task deleted successfully</info>');
        return Command::SUCCESS;
    });


$application->register('mark-in-progress')
    ->setDescription('Mark a task as in progress')
    ->addArgument('id', InputArgument::REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $id = $input->getArgument('id');

        $config = getConfig();

        if (!isset($config['TASK'][$id])) {
            $output->writeln('<error>Task not found</error>');
            return Command::FAILURE;
        }

        $config['TASK'][$id]['status'] = 'in-progress';
        $config['TASK'][$id]['updatedAt'] = date('Y-m-d H:i:s');

        setConfig($config);

        $output->writeln('<info>Task marked as in progress</info>');
        return Command::SUCCESS;
    });


$application->register('mark-done')
    ->setDescription('Mark a task as done')
    ->addArgument('id', InputArgument::REQUIRED)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $id = $input->getArgument('id');

        $config = getConfig();

        if (!isset($config['TASK'][$id])) {
            $output->writeln('<error>Task not found</error>');
            return Command::FAILURE;
        }

        $config['TASK'][$id]['status'] = 'done';
        $config['TASK'][$id]['updatedAt'] = date('Y-m-d H:i:s');

        setConfig($config);

        $output->writeln('<info>Task marked as done</info>');
        return Command::SUCCESS;
    });

$application->register('list')
    ->setDescription('List tasks')
    ->addArgument('status', InputArgument::OPTIONAL)
    ->setCode(function (InputInterface $input, OutputInterface $output): int {
        $status = $input->getArgument('status');
        $config = getConfig();

        $config = $config['TASK'];
        if ($status) {
            $config = array_filter($config['TASK'], function ($task) use ($status) {
                return $task['status'] === $status;
            });
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Description', 'Status', 'Created At', 'Updated At']);
        $table->setRows(array_map(function ($task) {
            return [
                $task['id'],
                $task['description'],
                $task['status'],
                $task['createdAt'],
                $task['updatedAt'],
            ];
        }, $config));
        $table->render();
        return Command::SUCCESS;
    });

$application->run();

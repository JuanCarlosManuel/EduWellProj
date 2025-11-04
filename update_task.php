<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $file = 'tasks.json';
  if (!file_exists($file)) exit(json_encode(['success' => false]));

  $tasks = json_decode(file_get_contents($file), true);
  $taskText = $_POST['task'] ?? '';
  $dateText = $_POST['date'] ?? '';
  $completed = filter_var($_POST['completed'] ?? false, FILTER_VALIDATE_BOOLEAN);

  foreach ($tasks as &$task) {
    if ($task['task'] === $taskText && $task['date'] === $dateText) {
      $task['completed'] = $completed;
      break;
    }
  }

  file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
  echo json_encode(['success' => true]);
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $file = 'tasks.json';
  $tasks = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

  $newTask = [
    'date' => $_POST['date'] ?? '',
    'task' => $_POST['task'] ?? '',
    'completed' => false
  ];

  $tasks[] = $newTask;
  file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
  echo json_encode(['success' => true]);
}
?>

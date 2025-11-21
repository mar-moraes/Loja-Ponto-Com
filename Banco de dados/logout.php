<?php
session_start();
session_unset();
session_destroy();
header("Location: ../src/index.php"); // Volta para a página inicial
exit();

<?php

// Senha que você deseja criptografar
$senha = "nova@2025";

// Gera o hash da senha usando PASSWORD_DEFAULT (algoritmo bcrypt, seguro e recomendado)
$hash_senha = password_hash($senha, PASSWORD_DEFAULT);

// Exibe o hash gerado
echo "O hash da senha \"{$senha}\" é:   
";
echo "<strong>" . $hash_senha . "</strong>";

// Instruções adicionais
echo "  
  
Copie o hash acima e use-o no seu comando SQL para inserir o usuário.";
echo "  
Lembre-se de DELETAR este arquivo do seu servidor após usá-lo, por segurança!";

?>

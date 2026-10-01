<?php
header("Content-Type: application/json");
require "conexao.php";
$metodo = $_SERVER["REQUEST_METHOD"];

if($metodo == "POST"){
    $json = file_get_contents("php://input");
    $dados = json_decode($json, true);

    // 1. Verifica se os dados obrigatórios foram informados
    if (
        empty($dados["nome"]) || 
        empty($dados["categoria"]) || 
        empty($dados["fornecedor"]) || 
        !isset($dados["quantidade"]) || 
        !isset($dados["preco_unitario"])
    ) {
        http_response_code(400);
        echo json_encode(["Erro" => "Todos os campos (nome, categoria, fornecedor, quantidade e preco_unitario) sao obrigatorios."]);
        exit();
    }

    // Padroniza a categoria para letras minúsculas para evitar erros de digitação (Ex: Eletrica vs eletrica)
    $categoria = strtolower(trim($dados["categoria"]));
    $quantidade = $dados["quantidade"];
    $preco_unitario = $dados["preco_unitario"];

    // 2. Validação da categoria: aceita apenas eletrica, mecanica ou hidraulica
    $categoriasPermitidas = ["eletrica", "mecanica", "hidraulica"];
    if (!in_array($categoria, $categoriasPermitidas)) {
        http_response_code(400);
        echo json_encode(["Erro" => "A categoria deve ser Eletrica, Mecanica ou Hidraulica."]);
        exit;
    }

    // 3. Validação da quantidade: não pode ser negativa
    if ($quantidade < 0) {
        http_response_code(400);
        echo json_encode(["Erro" => "A quantidade não podera ser negativa."]);
        exit;
    }


    // Se passou por todas as validações, executa o banco de dados
    $sql = "INSERT INTO pecas (nome, categoria, fornecedor, quantidade, preco_unitario) VALUES (?,?,?,?,?)";

    $comando = $pdo->prepare($sql);
    $comando->execute([
        $dados["nome"],  
        $categoria, // Usa a categoria já tratada e validada
        $dados["fornecedor"], 
        $quantidade, 
        $dados['preco_unitario']
    ]);

    echo json_encode(["Mensagem" => "Peça anotado com sucesso!"]);
}


if($metodo == "GET"){
    $sql = "SELECT * FROM pecas ORDER BY id";

    $comando = $pdo -> query($sql);

    $chamados = $comando -> fetchAll(PDO::FETCH_ASSOC);
//PDO::FETCH_ASSOC é pegar algo sem ser necessariamente um PDO
    echo json_encode($chamados);

};

if ($metodo == "PUT"){
    $json = file_get_contents("php://input");
     $dados = json_decode($json,true);

    $sql = "UPDATE pecas SET nome=?, categoria=?, fornecedor=?, quantidade=?, preco_unitario=? WHERE id=?";
    $comando = $pdo -> prepare($sql);
    $comando -> execute([$dados["nome"],
    $dados["categoria"],
    $dados["fornecedor"],
    $dados["quantidade"],
    $dados["preco_unitario"],
    $dados["id"]
    ]);

    echo json_encode(["mensagem" => "Peça atualizado com sucesso!"]);
};

if($metodo == "DELETE"){
    $json = file_get_contents("php://input");
    $dados = json_decode($json,true);

    $sql = "DELETE FROM pecas WHERE id=?";
    $comando = $pdo -> prepare($sql);
    $comando -> execute([$dados["id"]]);

    echo json_encode(["mensagem" => "Peça deletado com sucesso!"]);
};

//Quantas unidades de peças existem no almoxarifado, somando todas?

$sql="SELECT SUM(quantidade) AS total_unidades_almoxarifado 
FROM pecas;";


// Qual é o valor total do estoque, em reais?

$sql="SELECT SUM(quantidade * preco_unitario) AS valor_total_estoque_reais 
FROM pecas;";

//Qual é o preço unitário da peça mais cara?

$sql="SELECT MAX(preco_unitario) AS preco_peca_mais_cara 
FROM pecas;";

//Qual é o preço unitário da peça mais barata?

$sql="SELECT MIN(preco_unitario) AS preco_peca_mais_barata 
FROM pecas;";

// Qual é o preço médio das peças, com duas casas decimais?
// No PHP, a função round() serve para arredondar um número de ponto flutuante (float) ou inteiro (int) para o valor mais próximo
$sql="SELECT ROUND(AVG(preco_unitario), 2) AS preco_medio_pecas 
FROM pecas;";

//Qual é o valor total em estoque só das peças da categoria eletrica?

$sql="SELECT SUM(quantidade * preco_unitario) AS valor_total_estoque_eletrica 
FROM pecas 
WHERE categoria = 'eletrica';"
?>
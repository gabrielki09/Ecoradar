// Middleware central de tratamento de erros.
// Mantém as respostas de erro em um formato único e evita vazar detalhes internos.
function errorHandler(err, req, res, next) {
  console.error(err);

  if (err.nome === "ErroDeValidacao") {
    return res.status(400).json({ erro: err.message, detalhes: err.detalhes });
  }

  return res.status(500).json({ erro: "Erro interno do servidor." });
}

module.exports = errorHandler;

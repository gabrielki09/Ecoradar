const jwt = require("jsonwebtoken");

// Verifica o token JWT enviado no cabeçalho "Authorization: Bearer <token>".
// Em caso de sucesso, anexa os dados do usuário em req.usuario.
function autenticar(req, res, next) {
  const authHeader = req.headers.authorization;

  if (!authHeader) {
    return res.status(401).json({ erro: "Token não informado." });
  }

  const [, token] = authHeader.split(" ");

  try {
    const payload = jwt.verify(token, process.env.JWT_SECRET);
    req.usuario = payload; // { id, perfil }
    return next();
  } catch (err) {
    return res.status(401).json({ erro: "Token inválido ou expirado." });
  }
}

// Restringe uma rota a um ou mais perfis (ex: autorizar("COLETOR", "EMPRESA")).
function autorizar(...perfisPermitidos) {
  return (req, res, next) => {
    if (!req.usuario || !perfisPermitidos.includes(req.usuario.perfil)) {
      return res.status(403).json({ erro: "Acesso não permitido para este perfil." });
    }
    return next();
  };
}

module.exports = { autenticar, autorizar };

const prisma = require("../config/prisma");

async function listar(req, res, next) {
  try {
    const categorias = await prisma.categoriaResiduo.findMany({ orderBy: { nome: "asc" } });
    return res.json(categorias);
  } catch (err) {
    return next(err);
  }
}

module.exports = { listar };
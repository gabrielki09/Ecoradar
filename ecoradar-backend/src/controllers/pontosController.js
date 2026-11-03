const prisma = require("../config/prisma");

// GET /api/pontos-coleta?categoria=&bairro=&busca=
// Rota pública: lista pontos ativos, com filtros opcionais.
async function listar(req, res, next) {
  try {
    const { categoria, bairro, busca } = req.query;

    const pontos = await prisma.pontoColeta.findMany({
      where: {
        ativo: true,
        ...(bairro ? { bairro: { contains: bairro} } : {}),
        ...(busca
          ? {
              OR: [
                { nome: { contains: busca } },
                { endereco: { contains: busca } },
              ],
            }
          : {}),
        ...(categoria
          ? { categorias: { some: { categoriaId: categoria } } }
          : {}),
      },
      include: { categorias: { include: { categoria: true } } },
    });

    return res.json(pontos);
  } catch (err) {
    return next(err);
  }
}

// GET /api/pontos-coleta/:id
async function detalhar(req, res, next) {
  try {
    const ponto = await prisma.pontoColeta.findUnique({
      where: { id: req.params.id },
      include: { categorias: { include: { categoria: true } } },
    });

    if (!ponto) return res.status(404).json({ erro: "Ponto de coleta não encontrado." });

    return res.json(ponto);
  } catch (err) {
    return next(err);
  }
}

// POST /api/pontos-coleta — somente COLETOR autenticado
async function criar(req, res, next) {
  try {
    const { nome, endereco, bairro, horario, contato, categoriaIds } = req.body;

    const ponto = await prisma.pontoColeta.create({
      data: {
        nome,
        endereco,
        bairro,
        horario,
        contato,
        coletorId: req.usuario.id,
        categorias: {
          create: (categoriaIds || []).map((categoriaId) => ({ categoriaId })),
        },
      },
    });

    return res.status(201).json(ponto);
  } catch (err) {
    return next(err);
  }
}

// PUT /api/pontos-coleta/:id — somente o COLETOR dono do ponto
async function atualizar(req, res, next) {
  try {
    const ponto = await prisma.pontoColeta.findUnique({ where: { id: req.params.id } });
    if (!ponto) return res.status(404).json({ erro: "Ponto de coleta não encontrado." });
    if (ponto.coletorId !== req.usuario.id) {
      return res.status(403).json({ erro: "Você não tem permissão para editar este ponto." });
    }

    const atualizado = await prisma.pontoColeta.update({
      where: { id: req.params.id },
      data: req.body,
    });

    return res.json(atualizado);
  } catch (err) {
    return next(err);
  }
}

module.exports = { listar, detalhar, criar, atualizar };

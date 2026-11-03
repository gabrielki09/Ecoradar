const { z } = require("zod");
const prisma = require("../config/prisma");

const criarSchema = z.object({
  categoriaId: z.string().uuid("Categoria inválida."),
  quantidade: z.number().positive("Informe uma quantidade válida."),
  unidade: z.enum(["kg", "litros", "unidades"]),
  enderecoRetirada: z.string().min(5, "Informe o endereço para retirada."),
  dataPreferencial: z.string(), // ISO date
  periodo: z.string(),
  observacoes: z.string().optional(),
});

// POST /api/solicitacoes — usuário autenticado (perfil doador/empresa)
async function criar(req, res, next) {
  try {
    const dados = criarSchema.parse(req.body);

    const solicitacao = await prisma.solicitacao.create({
      data: {
        ...dados,
        dataPreferencial: new Date(dados.dataPreferencial),
        usuarioId: req.usuario.id,
      },
    });

    return res.status(201).json(solicitacao);
  } catch (err) {
    if (err.issues) {
      return res.status(400).json({ erro: "Dados inválidos.", detalhes: err.issues });
    }
    return next(err);
  }
}

// GET /api/solicitacoes/minhas — solicitações do usuário logado
async function minhas(req, res, next) {
  try {
    const solicitacoes = await prisma.solicitacao.findMany({
      where: { usuarioId: req.usuario.id },
      include: { categoria: true },
      orderBy: { criadoEm: "desc" },
    });
    return res.json(solicitacoes);
  } catch (err) {
    return next(err);
  }
}

// GET /api/solicitacoes/recebidas — solicitações visíveis para o coletor
// Observação: depende de regra de negócio sobre quais solicitações
// pertencem a qual coletor (ex: por região ou categoria aceita).
// Aqui retorna todas as pendentes/ativas como ponto de partida.
async function recebidas(req, res, next) {
  try {
    const solicitacoes = await prisma.solicitacao.findMany({
      where: { status: { in: ["PENDENTE", "CONFIRMADO", "EM_ROTA"] } },
      include: { categoria: true, usuario: { select: { nome: true, telefone: true } } },
      orderBy: { criadoEm: "asc" },
    });
    return res.json(solicitacoes);
  } catch (err) {
    return next(err);
  }
}

const statusValidos = ["PENDENTE", "CONFIRMADO", "EM_ROTA", "CONCLUIDO", "CANCELADO"];

// PATCH /api/solicitacoes/:id/status
async function atualizarStatus(req, res, next) {
  try {
    const { status } = req.body;
    if (!statusValidos.includes(status)) {
      return res.status(400).json({ erro: "Status inválido." });
    }

    const solicitacao = await prisma.solicitacao.update({
      where: { id: req.params.id },
      data: { status },
    });

    return res.json(solicitacao);
  } catch (err) {
    return next(err);
  }
}

module.exports = { criar, minhas, recebidas, atualizarStatus };

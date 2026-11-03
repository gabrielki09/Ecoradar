const prisma = require("../config/prisma");

// GET /api/impacto/publico — rota pública, sem login
// Agrega dados reais do banco. Enquanto houver poucos registros,
// o frontend deve sinalizar os números como exemplos.
async function publico(req, res, next) {
  try {
    const porCategoria = await prisma.solicitacao.groupBy({
      by: ["categoriaId"],
      where: { status: "CONCLUIDO" },
      _sum: { quantidade: true },
    });

    const categorias = await prisma.categoriaResiduo.findMany();
    const porCategoriaComNome = porCategoria.map((item) => ({
      categoria: categorias.find((c) => c.id === item.categoriaId)?.nome,
      totalKg: item._sum.quantidade,
    }));

    const totalPontos = await prisma.pontoColeta.count({ where: { ativo: true } });

    return res.json({ porCategoria: porCategoriaComNome, totalPontosAtivos: totalPontos });
  } catch (err) {
    return next(err);
  }
}

// GET /api/usuarios/me/painel — autenticado
async function painel(req, res, next) {
  try {
    const solicitacoes = await prisma.solicitacao.findMany({
      where: { usuarioId: req.usuario.id },
      include: { categoria: true },
      orderBy: { criadoEm: "desc" },
    });

    const pontos = await prisma.pontosUsuario.findUnique({
      where: { usuarioId: req.usuario.id },
    });

    return res.json({
      solicitacoes,
      pontosAcumulados: pontos?.total || 0,
    });
  } catch (err) {
    return next(err);
  }
}

module.exports = { publico, painel };

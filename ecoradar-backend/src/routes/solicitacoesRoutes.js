const { Router } = require("express");
const solicitacoesController = require("../controllers/solicitacoesController");
const { autenticar, autorizar } = require("../middlewares/auth");

const router = Router();

// Todas as rotas de solicitação exigem usuário autenticado.
router.post("/", autenticar, autorizar("DOADOR", "EMPRESA"), solicitacoesController.criar);
router.get("/minhas", autenticar, solicitacoesController.minhas);
router.get("/recebidas", autenticar, autorizar("COLETOR"), solicitacoesController.recebidas);
router.patch("/:id/status", autenticar, solicitacoesController.atualizarStatus);

module.exports = router;

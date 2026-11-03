const { Router } = require("express");
const pontosController = require("../controllers/pontosController");
const { autenticar, autorizar } = require("../middlewares/auth");

const router = Router();

// Rotas públicas (não exigem login)
router.get("/", pontosController.listar);
router.get("/:id", pontosController.detalhar);

// Rotas protegidas (somente coletor)
router.post("/", autenticar, autorizar("COLETOR"), pontosController.criar);
router.put("/:id", autenticar, autorizar("COLETOR"), pontosController.atualizar);

module.exports = router;

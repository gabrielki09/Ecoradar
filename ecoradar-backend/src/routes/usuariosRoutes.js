const { Router } = require("express");
const impactoController = require("../controllers/impactoController");
const { autenticar } = require("../middlewares/auth");

const router = Router();

// GET /api/usuarios/me/painel — dados do painel do usuário logado
router.get("/me/painel", autenticar, impactoController.painel);

module.exports = router;

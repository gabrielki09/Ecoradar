const { Router } = require("express");
const impactoController = require("../controllers/impactoController");
const { autenticar } = require("../middlewares/auth");

const router = Router();

// Pública — usada no painel público de impacto
router.get("/publico", impactoController.publico);

module.exports = router;

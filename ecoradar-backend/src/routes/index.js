const { Router } = require("express");

const authRoutes = require("./authRoutes");
const pontosRoutes = require("./pontosRoutes");
const solicitacoesRoutes = require("./solicitacoesRoutes");
const impactoRoutes = require("./impactoRoutes");
const usuariosRoutes = require("./usuariosRoutes");
const categoriasRoutes = require("./categoriasRoutes");

const router = Router();

router.use("/auth", authRoutes);
router.use("/pontos-coleta", pontosRoutes);
router.use("/solicitacoes", solicitacoesRoutes);
router.use("/impacto", impactoRoutes);
router.use("/usuarios", usuariosRoutes);
router.use("/categorias", categoriasRoutes);

module.exports = router;
const { Router } = require("express");
const categoriasController = require("../controllers/categoriasController");

const router = Router();

router.get("/", categoriasController.listar);

module.exports = router;
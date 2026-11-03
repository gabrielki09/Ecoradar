const { z } = require("zod");
const authService = require("../services/authService");

const registroSchema = z.object({
  nome: z.string().min(2, "Informe o nome completo."),
  email: z.string().email("Informe um e-mail válido."),
  senha: z.string().min(8, "A senha deve ter ao menos 8 caracteres."),
  perfil: z.enum(["DOADOR", "EMPRESA", "COLETOR"]),
  cpfCnpj: z.string().optional(),
  endereco: z.string().optional(),
  telefone: z.string().optional(),
});

const loginSchema = z.object({
  email: z.string().email("Informe um e-mail válido."),
  senha: z.string().min(1, "Informe a senha."),
});

async function registro(req, res, next) {
  try {
    const dados = registroSchema.parse(req.body);
    const resultado = await authService.registrar(dados);
    return res.status(201).json(resultado);
  } catch (err) {
    if (err.issues) {
      return res.status(400).json({ erro: "Dados inválidos.", detalhes: err.issues });
    }
    return next(err);
  }
}

async function login(req, res, next) {
  try {
    const dados = loginSchema.parse(req.body);
    const resultado = await authService.login(dados);
    return res.status(200).json(resultado);
  } catch (err) {
    if (err.issues) {
      return res.status(400).json({ erro: "Dados inválidos.", detalhes: err.issues });
    }
    return next(err);
  }
}

module.exports = { registro, login };

const bcrypt = require("bcrypt");
const jwt = require("jsonwebtoken");
const prisma = require("../config/prisma");

const SALT_ROUNDS = 10;

async function registrar({ nome, email, senha, perfil, cpfCnpj, endereco, telefone }) {
  const existente = await prisma.usuario.findUnique({ where: { email } });
  if (existente) {
    const erro = new Error("Este e-mail já está cadastrado.");
    erro.nome = "ErroDeValidacao";
    throw erro;
  }

  const senhaHash = await bcrypt.hash(senha, SALT_ROUNDS);

  const usuario = await prisma.usuario.create({
    data: { nome, email, senhaHash, perfil, cpfCnpj, endereco, telefone },
  });

  return gerarResposta(usuario);
}

async function login({ email, senha }) {
  const usuario = await prisma.usuario.findUnique({ where: { email } });
  if (!usuario) {
    const erro = new Error("E-mail ou senha inválidos.");
    erro.nome = "ErroDeValidacao";
    throw erro;
  }

  const senhaConfere = await bcrypt.compare(senha, usuario.senhaHash);
  if (!senhaConfere) {
    const erro = new Error("E-mail ou senha inválidos.");
    erro.nome = "ErroDeValidacao";
    throw erro;
  }

  return gerarResposta(usuario);
}

function gerarResposta(usuario) {
  const token = jwt.sign(
    { id: usuario.id, perfil: usuario.perfil },
    process.env.JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRES_IN || "7d" }
  );

  return {
    token,
    usuario: {
      id: usuario.id,
      nome: usuario.nome,
      email: usuario.email,
      perfil: usuario.perfil,
    },
  };
}

module.exports = { registrar, login };

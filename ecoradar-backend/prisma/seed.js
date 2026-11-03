// Popula o banco com as categorias de resíduo usadas pelo frontend.
// Execute com: node prisma/seed.js
const { PrismaClient } = require("@prisma/client");
const prisma = new PrismaClient();

const CATEGORIAS = [
  "Lixo eletrônico",
  "Pilhas e baterias",
  "Óleo de cozinha usado",
  "Vidro",
  "Plástico",
  "Papel e papelão",
  "Metal",
  "Outros materiais recicláveis",
];

async function main() {
  for (const nome of CATEGORIAS) {
    await prisma.categoriaResiduo.upsert({
      where: { nome },
      update: {},
      create: { nome },
    });
  }
  console.log("Categorias de resíduo criadas/atualizadas.");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });

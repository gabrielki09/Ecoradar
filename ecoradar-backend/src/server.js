require("dotenv").config();
const express = require("express");
const cors = require("cors");
const routes = require("./routes");
const errorHandler = require("./middlewares/errorHandler");

const app = express();

app.use(cors({ origin: true }));
app.use(express.json());

app.get("/", (req, res) => {
  res.json({ status: "ok", projeto: "EcoRadar API" });
});

app.use("/api", routes);

// Deve ser o último middleware registrado.
app.use(errorHandler);

const PORT = process.env.PORT || 3333;
app.listen(PORT, () => {
  console.log(`EcoRadar API rodando em http://localhost:${PORT}`);
});

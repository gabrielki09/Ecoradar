BEGIN TRY

BEGIN TRAN;

-- CreateTable
CREATE TABLE [dbo].[usuarios] (
    [id] NVARCHAR(1000) NOT NULL,
    [nome] NVARCHAR(1000) NOT NULL,
    [email] NVARCHAR(1000) NOT NULL,
    [senhaHash] NVARCHAR(1000) NOT NULL,
    [perfil] NVARCHAR(1000) NOT NULL,
    [cpfCnpj] NVARCHAR(1000),
    [endereco] NVARCHAR(1000),
    [telefone] NVARCHAR(1000),
    [criadoEm] DATETIME2 NOT NULL CONSTRAINT [usuarios_criadoEm_df] DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT [usuarios_pkey] PRIMARY KEY CLUSTERED ([id]),
    CONSTRAINT [usuarios_email_key] UNIQUE NONCLUSTERED ([email])
);

-- CreateTable
CREATE TABLE [dbo].[categorias_residuo] (
    [id] NVARCHAR(1000) NOT NULL,
    [nome] NVARCHAR(1000) NOT NULL,
    CONSTRAINT [categorias_residuo_pkey] PRIMARY KEY CLUSTERED ([id]),
    CONSTRAINT [categorias_residuo_nome_key] UNIQUE NONCLUSTERED ([nome])
);

-- CreateTable
CREATE TABLE [dbo].[pontos_coleta] (
    [id] NVARCHAR(1000) NOT NULL,
    [nome] NVARCHAR(1000) NOT NULL,
    [endereco] NVARCHAR(1000) NOT NULL,
    [bairro] NVARCHAR(1000),
    [horario] NVARCHAR(1000),
    [contato] NVARCHAR(1000),
    [ativo] BIT NOT NULL CONSTRAINT [pontos_coleta_ativo_df] DEFAULT 1,
    [coletorId] NVARCHAR(1000) NOT NULL,
    [criadoEm] DATETIME2 NOT NULL CONSTRAINT [pontos_coleta_criadoEm_df] DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT [pontos_coleta_pkey] PRIMARY KEY CLUSTERED ([id])
);

-- CreateTable
CREATE TABLE [dbo].[ponto_categorias] (
    [pontoId] NVARCHAR(1000) NOT NULL,
    [categoriaId] NVARCHAR(1000) NOT NULL,
    CONSTRAINT [ponto_categorias_pkey] PRIMARY KEY CLUSTERED ([pontoId],[categoriaId])
);

-- CreateTable
CREATE TABLE [dbo].[solicitacoes_coleta] (
    [id] NVARCHAR(1000) NOT NULL,
    [usuarioId] NVARCHAR(1000) NOT NULL,
    [categoriaId] NVARCHAR(1000) NOT NULL,
    [quantidade] FLOAT(53) NOT NULL,
    [unidade] NVARCHAR(1000) NOT NULL,
    [enderecoRetirada] NVARCHAR(1000) NOT NULL,
    [dataPreferencial] DATETIME2 NOT NULL,
    [periodo] NVARCHAR(1000) NOT NULL,
    [observacoes] NVARCHAR(1000),
    [status] NVARCHAR(1000) NOT NULL CONSTRAINT [solicitacoes_coleta_status_df] DEFAULT 'PENDENTE',
    [criadoEm] DATETIME2 NOT NULL CONSTRAINT [solicitacoes_coleta_criadoEm_df] DEFAULT CURRENT_TIMESTAMP,
    [atualizadoEm] DATETIME2 NOT NULL,
    CONSTRAINT [solicitacoes_coleta_pkey] PRIMARY KEY CLUSTERED ([id])
);

-- CreateTable
CREATE TABLE [dbo].[pontos_usuario] (
    [usuarioId] NVARCHAR(1000) NOT NULL,
    [total] INT NOT NULL CONSTRAINT [pontos_usuario_total_df] DEFAULT 0,
    CONSTRAINT [pontos_usuario_pkey] PRIMARY KEY CLUSTERED ([usuarioId])
);

-- CreateTable
CREATE TABLE [dbo].[recompensas] (
    [id] NVARCHAR(1000) NOT NULL,
    [nome] NVARCHAR(1000) NOT NULL,
    [pontosNecessarios] INT NOT NULL,
    CONSTRAINT [recompensas_pkey] PRIMARY KEY CLUSTERED ([id])
);

-- CreateTable
CREATE TABLE [dbo].[resgates] (
    [id] NVARCHAR(1000) NOT NULL,
    [usuarioId] NVARCHAR(1000) NOT NULL,
    [recompensaId] NVARCHAR(1000) NOT NULL,
    [dataResgate] DATETIME2 NOT NULL CONSTRAINT [resgates_dataResgate_df] DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT [resgates_pkey] PRIMARY KEY CLUSTERED ([id])
);

-- AddForeignKey
ALTER TABLE [dbo].[pontos_coleta] ADD CONSTRAINT [pontos_coleta_coletorId_fkey] FOREIGN KEY ([coletorId]) REFERENCES [dbo].[usuarios]([id]) ON DELETE NO ACTION ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE [dbo].[ponto_categorias] ADD CONSTRAINT [ponto_categorias_pontoId_fkey] FOREIGN KEY ([pontoId]) REFERENCES [dbo].[pontos_coleta]([id]) ON DELETE CASCADE ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE [dbo].[ponto_categorias] ADD CONSTRAINT [ponto_categorias_categoriaId_fkey] FOREIGN KEY ([categoriaId]) REFERENCES [dbo].[categorias_residuo]([id]) ON DELETE NO ACTION ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE [dbo].[solicitacoes_coleta] ADD CONSTRAINT [solicitacoes_coleta_usuarioId_fkey] FOREIGN KEY ([usuarioId]) REFERENCES [dbo].[usuarios]([id]) ON DELETE NO ACTION ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE [dbo].[solicitacoes_coleta] ADD CONSTRAINT [solicitacoes_coleta_categoriaId_fkey] FOREIGN KEY ([categoriaId]) REFERENCES [dbo].[categorias_residuo]([id]) ON DELETE NO ACTION ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE [dbo].[pontos_usuario] ADD CONSTRAINT [pontos_usuario_usuarioId_fkey] FOREIGN KEY ([usuarioId]) REFERENCES [dbo].[usuarios]([id]) ON DELETE NO ACTION ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE [dbo].[resgates] ADD CONSTRAINT [resgates_usuarioId_fkey] FOREIGN KEY ([usuarioId]) REFERENCES [dbo].[usuarios]([id]) ON DELETE NO ACTION ON UPDATE CASCADE;

-- AddForeignKey
ALTER TABLE [dbo].[resgates] ADD CONSTRAINT [resgates_recompensaId_fkey] FOREIGN KEY ([recompensaId]) REFERENCES [dbo].[recompensas]([id]) ON DELETE NO ACTION ON UPDATE CASCADE;

COMMIT TRAN;

END TRY
BEGIN CATCH

IF @@TRANCOUNT > 0
BEGIN
    ROLLBACK TRAN;
END;
THROW

END CATCH

# Instruções de Instalação - CANNAL Espetáculos

## 📦 Instalação via FTP

1. **Baixe o plugin** do repositório GitHub
2. **Extraia o arquivo ZIP** (se necessário)
3. **Conecte-se ao seu servidor** via FTP usando as credenciais fornecidas
4. **Navegue até** `/wp-content/plugins/`
5. **Envie a pasta** `cannal-espetaculos` para o diretório de plugins
6. **Acesse o painel do WordPress**
7. Vá em **Plugins > Plugins Instalados**
8. **Localize** "CANNAL Espetáculos"
9. Clique em **Ativar**

## ✅ Verificação Pós-Instalação

Após ativar o plugin, verifique:

1. **Menu lateral**: Deve aparecer "CANNAL Espetáculos" com submenus:
   - Espetáculos
   - Temporadas
   - Categorias

2. **URLs**: Acesse Configurações > Links Permanentes e clique em "Salvar Alterações" para garantir que as URLs funcionem corretamente

3. **Elementor** (se instalado): Ao editar uma página de espetáculo com Elementor, deve aparecer a categoria "CANNAL Espetáculos" com 7 widgets

## 🎯 Primeiros Passos

### 1. Criar Categorias (Opcional)
- Acesse **CANNAL Espetáculos > Categorias**
- Adicione categorias como "Teatro Adulto", "Teatro Infantil", etc.
- **Importante**: A estrutura de URLs muda automaticamente quando você cria ou remove categorias

### 2. Criar seu Primeiro Espetáculo
- Acesse **CANNAL Espetáculos > Adicionar Novo**
- Preencha o título e o conteúdo
- Adicione uma **Imagem Destacada** (será usada no banner)
- Preencha os **Detalhes do Espetáculo**
- Adicione fotos na **Galeria**
- Publique

### 3. Criar uma Temporada
- Acesse **CANNAL Espetáculos > Temporadas > Adicionar Nova**
- Selecione o espetáculo criado
- Preencha os dados do teatro e datas
- Configure as sessões (avulsas ou temporada)
- Publique

### 4. Visualizar
- Acesse `/espetaculos/` no seu site para ver o arquivo
- Clique no espetáculo para ver a página individual

## 🎨 Personalização com Elementor

Se você usa Elementor:

1. **Edite a página do espetáculo** com Elementor
2. Arraste os widgets da categoria **CANNAL Espetáculos**
3. Personalize cores, tipografia e layout
4. Salve e visualize

## 🎬 Configuração do RevSlider

Para usar os banners automáticos:

1. **Crie um novo slider** no RevSlider
2. **Adicione slides** manualmente ou use o shortcode:
   ```
   [cannal_banner_espetaculos limit="10"]
   ```
3. Configure o slider para exibir na home ou onde desejar

### Dados Disponíveis por Slide
Cada espetáculo em cartaz ou futuro fornece:
- Imagem destacada (background)
- Título do espetáculo
- Nome do teatro
- Dias e horários
- Link de ingressos
- Link para a página do espetáculo

## ⚠️ Problemas Comuns

### URLs não funcionam (404)
**Solução**: Vá em Configurações > Links Permanentes e clique em "Salvar Alterações"

### Widgets do Elementor não aparecem
**Solução**: Certifique-se de que o Elementor está instalado e ativado. Limpe o cache do Elementor.

### Imagens não aparecem
**Solução**: Verifique as permissões da pasta `wp-content/uploads/`

### Temporadas não aparecem no espetáculo
**Solução**: Certifique-se de que a temporada está vinculada ao espetáculo correto e publicada

## 🔄 Atualização

Para atualizar o plugin:

1. **Desative** o plugin no WordPress
2. **Substitua** a pasta `cannal-espetaculos` via FTP pela nova versão
3. **Reative** o plugin
4. Acesse **Configurações > Links Permanentes** e salve novamente

## 🆘 Suporte

Se encontrar problemas:

1. Verifique a versão do WordPress (mínimo 5.0)
2. Verifique a versão do PHP (mínimo 7.4)
3. Desative outros plugins para identificar conflitos
4. Ative o modo de debug do WordPress
5. Consulte o arquivo README.md para mais informações

## 📞 Contato

Para suporte técnico, abra uma issue no repositório GitHub do projeto.

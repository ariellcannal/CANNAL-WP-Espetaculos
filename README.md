# CANNAL Espetáculos

Plugin WordPress completo para gerenciamento de espetáculos teatrais com temporadas, sessões, integração com Elementor e RevSlider.

## 📋 Características

- **Custom Post Types**: Espetáculos e Temporadas
- **Taxonomias**: Categorias de Espetáculos
- **Campos Personalizados**: Autor, ano de estreia, duração, classificação indicativa, galeria de fotos
- **Sistema de Temporadas**: Gerenciamento completo de temporadas por espetáculo
- **Sessões Flexíveis**: Suporte para sessões avulsas e temporadas regulares
- **URLs Dinâmicas**: Estrutura de URLs que se adapta automaticamente à presença de categorias
- **Templates Personalizados**: Single e archive pages com design responsivo
- **Integração Elementor**: 7 widgets personalizados para construção de páginas
- **Integração RevSlider**: Sistema automático de banners com espetáculos em cartaz

## 🚀 Instalação

1. Faça o download do plugin
2. Envie a pasta `cannal-espetaculos` para `/wp-content/plugins/`
3. Ative o plugin através do menu 'Plugins' no WordPress
4. As URLs serão automaticamente configuradas

## 📖 Uso

### Criando um Espetáculo

1. Acesse **CANNAL Espetáculos > Espetáculos** no menu do WordPress
2. Clique em **Adicionar Novo**
3. Preencha os campos:
   - **Título**: Nome do espetáculo
   - **Conteúdo**: Release/sinopse do espetáculo
   - **Imagem Destacada**: Banner principal (usado no RevSlider)
   - **Autor**: Nome do autor/dramaturgo
   - **Ano de Estreia**: Ano da primeira apresentação
   - **Duração**: Duração do espetáculo (ex: "90 minutos")
   - **Classificação Indicativa**: Livre, 10, 12, 14, 16 ou 18 anos
   - **Galeria de Fotos**: Imagens do espetáculo
4. Adicione tags e categorias conforme necessário
5. Publique

### Criando uma Temporada

1. Acesse **CANNAL Espetáculos > Temporadas**
2. Clique em **Adicionar Nova**
3. Selecione o **Espetáculo** vinculado
4. Preencha os campos:
   - **Nome do Teatro**: Local da apresentação
   - **Endereço do Teatro**: Endereço completo
   - **Data de Início**: Primeira apresentação
   - **Data Final**: Última apresentação
   - **Valores**: Informações sobre ingressos
   - **Link de Vendas**: URL para compra de ingressos
   - **Texto de Exibição do Link**: Texto do botão (padrão: "Ingressos Aqui")
   - **Data de Início do Banner**: Quando o banner deve começar a aparecer
5. Configure as **Sessões**:
   - **Sessões Avulsas**: Selecione datas específicas e horários
   - **Temporada**: Defina dias da semana e horários regulares
6. Publique

O título da temporada será gerado automaticamente no formato: "Nome do Teatro - Nome do Espetáculo"

### Estrutura de URLs

O plugin ajusta automaticamente a estrutura de URLs baseado na existência de categorias:

#### COM Categorias:
- `/espetaculos/` - Arquivo de categorias
- `/espetaculos/{categoria}/` - Espetáculos da categoria
- `/espetaculos/{categoria}/{espetaculo}/` - Single do espetáculo

#### SEM Categorias:
- `/espetaculos/` - Arquivo de espetáculos
- `/espetaculos/{espetaculo}/` - Single do espetáculo

## 🎨 Widgets do Elementor

O plugin adiciona uma categoria "CANNAL Espetáculos" no Elementor com 7 widgets:

### 1. Release
Exibe o conteúdo da temporada ativa ou do espetáculo.

### 2. Galeria de Fotos
Mostra a galeria de fotos do espetáculo em grid responsivo.
- **Configurações**: Número de colunas, espaçamento, raio da borda

### 3. Informação
Exibe uma informação específica do espetáculo ou temporada.
- **Opções**: Autor, ano, duração, classificação, teatro, endereço, temporada, valores, link de ingressos
- **Personalizável**: Título, tag HTML, cores, tipografia

### 4. Lista de Informações
Exibe múltiplas informações em formato de lista.
- **Configurações**: Adicione quantas informações desejar
- **Personalizável**: Títulos customizados, cores, tipografia

### 5. Próximas Apresentações
Lista as temporadas futuras do espetáculo.
- **Configurações**: Mostrar/ocultar teatro e data

### 6. Últimas Apresentações
Lista as temporadas encerradas do espetáculo.
- **Configurações**: Limite de itens exibidos

### 7. Em Cartaz
Exibe as temporadas atualmente em cartaz.
- **Configurações**: Mostrar/ocultar dias e horários
- **Personalizável**: Cores de fundo e texto

## 🎬 Integração com RevSlider

O plugin fornece dados automáticos para criação de banners no RevSlider.

### Shortcode
```
[cannal_banner_espetaculos limit="10"]
```

### Ordem de Exibição
1. Espetáculos **em cartaz** (ordenados por data de estreia)
2. Espetáculos com **apresentações futuras** (ordenados por data de estreia)

### Dados Disponíveis
- Imagem destacada do espetáculo (tela cheia)
- Nome do espetáculo
- Nome do teatro
- Dias e horários
- Botão de ingressos (se configurado)
- Link para a página do espetáculo

### Atualização Automática
O plugin atualiza automaticamente qual temporada deve ser exibida no banner ao salvar uma temporada.

## 🎭 Templates

O plugin inclui templates padrão que podem ser sobrescritos pelo tema:

- `single-espetaculo.php` - Página individual do espetáculo
- `archive-espetaculo.php` - Arquivo de espetáculos
- `archive-espetaculos-categories.php` - Arquivo de categorias

Para sobrescrever, copie o arquivo para a pasta do seu tema e customize.

## 🎨 Classificação Indicativa

O plugin exibe selos oficiais de classificação indicativa em HTML/CSS:

- **Livre**: Verde (#00a651)
- **10 anos**: Azul (#0093dd)
- **12 anos**: Amarelo (#ffd500)
- **14 anos**: Laranja (#ff8c00)
- **16 anos**: Vermelho (#e50914)
- **18 anos**: Preto (#000000)

## 🔧 Requisitos

- WordPress 5.0 ou superior
- PHP 7.4 ou superior
- Elementor (opcional, para widgets)
- RevSlider (opcional, para banners)

## 📝 Changelog

### 1.0.0
- Lançamento inicial
- Custom post types e taxonomias
- Sistema de temporadas e sessões
- Integração com Elementor
- Integração com RevSlider
- Templates responsivos

## 👨‍💻 Desenvolvimento

Este plugin foi desenvolvido seguindo as melhores práticas do WordPress:

- Arquitetura orientada a objetos
- Separação de responsabilidades
- Hooks e filtros do WordPress
- Segurança (nonces, sanitização, validação)
- Internacionalização pronta

## 📄 Licença

GPL-2.0+

## 🆘 Suporte

Para suporte, abra uma issue no repositório GitHub.

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, abra um pull request com suas melhorias.

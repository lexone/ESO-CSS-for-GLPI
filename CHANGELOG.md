# Changelog

## 1.9.2 - 2026-08-27

- Adicionada a opção **Ocultar a logo padrão “GLPI”** na personalização da tela de login.
- Uma logo personalizada continua visível mesmo quando a logo padrão está ocultada.
- Corrigido o estilo **Vidro** para respeitar as posições central, esquerda e direita configuradas.
- Corrigida a prévia administrativa para representar o alinhamento e a visibilidade da logo escolhidos.

## 1.9.1 - 2026-08-27

- Corrigida a exibição de imagens personalizadas para usuários anônimos na tela de login.
- Substituída a rota protegida `front/pluginimage.send.php` por um endpoint público, somente leitura e restrito aos nomes de imagens gerenciados pelo plugin.
- O novo endereço `/plugins/esocss/media/` não expõe a extensão PHP na URL, reduzindo conflitos com regras WAF.
- Adicionadas validações de caminho, formato real, tamanho, cache, `nosniff` e isolamento de origem ao servir JPG, PNG e WebP.

## 1.9.0 - 2026-08-27

- Adicionado o seletor **Estilo da tela de login** na interface administrativa.
- Adicionado o modelo **Vidro central**, com fotografia em tela cheia e um único cartão translúcido.
- Adicionado o modelo **Portal lateral**, com painel de acesso lateral e título/subtítulo sobre a área livre da fotografia.
- Mantido o modelo personalizado anterior como opção de compatibilidade.
- Os novos estilos reutilizam as imagens, logotipo, cores, transparência e textos configurados, sem alterar o fluxo de autenticação do GLPI ou do Entra ID.
- Adicionadas prévias responsivas dos novos estilos e adaptação automática para telas menores.

## 1.8.0 - 2026-08-26

- Adicionado o modo de imagem em painel separado, sem transformar a imagem em fundo da página.
- Adicionadas posições central, esquerda e direita para a área de login.
- Adicionado controle da largura do painel de imagem, com adaptação automática para celulares e tablets.
- Mantido o modo de imagem de fundo para compatibilidade com configurações anteriores.

## 1.7.3 - 2026-08-26

- Removidos exemplos e dados institucionais específicos do código, dos testes e das telas do plugin.
- Preparado um novo histórico público neutro, preservando apenas a base inicial e esta versão sanitizada.
- O pacote de produção agora exclui arquivos de desenvolvimento e de testes.

## 1.7.2 - 2026-08-26

- Corrigido o carregamento do CSS e do JavaScript na página anônima por meio dos ganchos específicos do GLPI 11.
- Corrigida a aplicação da imagem de fundo, título, logotipo, textos e rodapé configurados na tela de login.
- Adicionado suporte direto à estrutura do plugin Single Sign-On/Entra ID (`.singlesignon-*`).
- Separadas as larguras do cartão externo e do painel interno de autenticação, com prévia administrativa.
- Adicionadas cores independentes para fundo e texto dos botões principais, nos estados normal e hover.
- O texto e os ícones dos botões principais passam a usar branco por padrão, corrigindo o botão Salvar com texto escuro.
- Atualizações preservam as antigas cores primária e hover como cores iniciais dos novos controles de botão.
- Adicionado teste automatizado para impedir regressão dos recursos anônimos de CSS e JavaScript.

## 1.7.1 - 2026-08-26

- Adicionado atalho direto **ESO CSS for GLPI** dentro do menu Configuração do GLPI 11.
- O atalho abre diretamente a interface web do plugin e usa o ícone de paleta.
- A entrada é exibida somente para usuários autenticados com permissão de atualização da configuração global.
- Mantido o acesso existente pela lista de plugins.
- Adicionado teste automatizado para validar endereço, ícone e restrição de permissão do menu.

## 1.7.0 - 2026-08-26

- Adicionada troca do logotipo do menu lateral aberto do GLPI 11.
- Adicionada imagem independente para o menu lateral recolhido, com fallback automático para a logo principal.
- Adicionada troca do logotipo do cabeçalho horizontal e do Helpdesk.
- Adicionada personalização do ícone da aba do navegador, inclusive na tela anônima.
- Adicionados controles de altura e tamanho para cada variação de logo.
- Adicionada prévia administrativa conjunta de menu aberto, menu recolhido, cabeçalho e favicon.
- Mantida prioridade das logos específicas da página inicial e da tela de login.
- Novas imagens usam o armazenamento seguro e persistente já adotado pelo plugin.

## 1.6.0 - 2026-08-26

- Adicionada personalização dos textos visíveis da tela de login.
- Adicionados textos configuráveis para botão Entra ID/SSO, permanência e alternância ao formulário GLPI.
- Adicionados mensagem de boas-vindas, rótulos e exemplos de usuário/senha, origem de autenticação e botão Entrar.
- Adicionados textos configuráveis para recuperação de senha, FAQ e rodapé.
- Campos vazios preservam as traduções e mensagens originais do GLPI ou do provedor de autenticação.
- A substituição altera somente texto simples sanitizado e mantém os elementos, links e ações originais.
- A prévia passa a exibir simultaneamente os textos do login único e do formulário nativo.

## 1.5.0 - 2026-08-26

- Adicionada personalização responsiva da tela anônima de login do GLPI 11.
- Adicionados fundo, logotipo, título, subtítulo, cores, transparência, largura e cantos configuráveis.
- Adicionada prévia administrativa inspirada no fluxo de login único/Entra ID.
- Mantidos intactos os botões, links, tokens e regras dos provedores de autenticação existentes.
- A integração usa o gancho oficial `display_login` e não substitui arquivos do core do GLPI.
- O login recebe apenas uma carga sanitizada de identidade visual, sem expor CSS personalizado ou configurações administrativas.
- Adicionados uploads seguros e persistentes para o fundo e o logotipo da tela de login.

## 1.4.0 - 2026-08-26

- Adicionada personalização visual da página inicial Helpdesk do GLPI 11.
- Adicionados título, subtítulo, cor, tamanho, altura do banner, posição da imagem e intensidade da sobreposição.
- Adicionado upload de imagem de fundo e logotipo em JPG, PNG ou WebP, com limite de 5 MB e validação de conteúdo e dimensões.
- Imagens passam a ser armazenadas em `files/_plugins/esocss`, preservando os arquivos durante atualizações do plugin.
- Adicionada troca atômica das imagens e opção de restauração do visual nativo.
- Adicionada prévia da página inicial na interface administrativa.
- Mantida compatibilidade com as ilustrações laterais configuráveis por entidade no próprio GLPI.

## 1.3.1 - 2026-08-26

- Separada a interface `settings.php` do processamento `settings.form.php`; `config.php` agora existe apenas para redirecionar links antigos.
- Adicionado tratamento de erro no salvamento, com registro no log do GLPI e retorno legível ao administrador.
- Adicionada consulta da versão mais recente no repositório oficial do GitHub.
- Adicionado botão de atualização automática com confirmação administrativa.
- Quando o componente Git do GLPI estiver disponível, a atualização Git exige remote oficial e pasta sem alterações locais; nos demais ambientes, é usado o pacote verificado.
- Atualizações por pacote exigem release estável, URL oficial e checksum SHA-256 válido.
- Adicionado empacotamento automático de releases ao publicar tags `v*`.

## 1.3.0 - 2026-08-26

- Corrigida a herança de texto claro do cabeçalho dentro dos menus suspensos.
- Adicionadas cores independentes para fundo, texto e hover dos menus.
- Adicionadas cores configuráveis para os textos do cabeçalho e da sidebar.
- Adicionados os temas rápidos ESO Azul, Oceano e Grafite.
- Ampliada a detecção de instâncias Apache ECharts fora do seletor legado `.g-chart .chart`.
- Gráficos passam a respeitar as cores configuradas para cards, bordas e texto secundário.
- Corrigidas as URLs de instalação e a página do projeto no GitHub.
- Adicionadas validações automáticas de PHP, JavaScript, Bash e sanitização das configurações.

## 1.2.0 - 2026-08-25

- Identidade do projeto consolidada como **ESO CSS for GLPI**.
- Identificador técnico definido como `esocss`.
- Diretório do plugin definido como `esocss`.
- Namespace PHP definido como `GlpiPlugin\\EsoCss`.
- Arquivos públicos renomeados para `eso-modern.css` e `eso-theme.js`.
- Contexto de configuração definido como `plugin:esocss`.
- Interface web de configuração e integração com Apache ECharts mantidas.

## 1.1.0

- Interface web de configuração.
- Cores da sidebar, cabeçalho, fundo, cards, textos e bordas configuráveis.
- CSS personalizado administrável pela interface.
- Paleta ECharts configurável em 8 cores.
- Tema automático para gráficos de pizza, donut, barras e linhas do GLPI 11.
- Ajustes de raio, sombra e largura de barras.
- Prévia visual na tela de configuração.
- Instalação e atualização via scripts de terminal.

## 1.0.0

- Primeira versão do ESO CSS for GLPI para GLPI 11.

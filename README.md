# ESO CSS for GLPI

Plugin de personalização visual para **GLPI 11**, criado para modernizar a interface sem alterar arquivos do core.

**Autor:** Everton Silva de Oliveira<br>
**Identificador técnico do plugin:** `esocss`<br>
**Versão:** 1.9.0<br>
**Compatibilidade:** GLPI 11.0.0 até 11.0.99

## Recursos

- Sidebar com gradiente e estados hover/ativo.
- Cabeçalho configurável.
- Menus suspensos com fundo e texto independentes do cabeçalho, evitando o problema de menus brancos.
- Fundo, cards, bordas e textos personalizáveis.
- Fundo e texto dos botões principais configuráveis separadamente, inclusive no hover.
- Raio dos cards e intensidade da sombra configuráveis.
- CSS personalizado pela interface web.
- Paleta de 8 cores para gráficos.
- Tema automático para **Apache ECharts**, utilizado pelo Dashboard do GLPI 11.
- Suporte a gráficos de pizza, donut, barras horizontais/verticais e linhas.
- Interface visual de configuração com prévia.
- Atalho direto em **Configuração → ESO CSS for GLPI**, visível somente para administradores autorizados.
- Temas rápidos ESO Azul, Oceano e Grafite.
- Verificação de versão e atualização automática pela interface administrativa.
- Personalização da página inicial Helpdesk com fundo, logotipo, título, subtítulo e prévia.
- Três estilos selecionáveis para a tela de login: personalizado atual, Vidro central e Portal lateral.
- Personalização da tela de login com imagem em painel separado ou tela cheia, posição central/esquerda/direita, logotipo, textos, cores e prévia responsiva.
- Compatibilidade visual com login único/Entra ID sem alterar o fluxo de autenticação.
- Textos personalizáveis para SSO, formulário nativo, permanência, recuperação, FAQ e rodapé.
- Logotipos globais configuráveis para menu lateral aberto, menu recolhido e cabeçalho/Helpdesk.
- Ícone da aba do navegador configurável, inclusive na tela anônima de login.
- Upload seguro de imagens JPG, PNG e WebP, preservadas fora da pasta do plugin.
- Sem alteração do core do GLPI.
- Configurações persistidas no contexto `plugin:esocss` da tabela de configurações do GLPI.

## Estrutura

```text
esocss/
├── setup.php
├── hook.php
├── composer.json
├── .gitattributes
├── README.md
├── CHANGELOG.md
├── LICENSE
├── src/
│   ├── Settings.php
│   ├── MediaManager.php
│   └── Updater.php
├── front/
│   ├── config-data.php
│   ├── config.php
│   ├── settings.php
│   ├── settings.form.php
│   └── update.form.php
├── public/
│   ├── css/
│   │   └── eso-modern.css
│   └── js/
│       └── eso-theme.js
├── scripts/
│   ├── install.sh
│   └── update.sh
├── tests/
│   ├── settings-smoke.php
│   ├── setup-smoke.php
│   ├── install-smoke.php
│   └── fixtures/
│       └── dropdown.html
└── .github/
    └── workflows/
        └── php-lint.yml
```

## Instalação via GitHub

No servidor GLPI:

```bash
cd /var/www/html/plugins

git clone https://github.com/lexone/ESO-CSS-for-GLPI.git esocss

cd esocss
sudo ./scripts/install.sh /var/www/html
```

O nome do diretório deve ser **`esocss`**.

### Instalação manual

```bash
cd /var/www/html/plugins

git clone https://github.com/lexone/ESO-CSS-for-GLPI.git esocss

chown -R www-data:www-data /var/www/html/plugins/esocss
find /var/www/html/plugins/esocss -type d -exec chmod 755 {} \;
find /var/www/html/plugins/esocss -type f -exec chmod 644 {} \;

cd /var/www/html
sudo -u www-data php bin/console glpi:plugin:install esocss
sudo -u www-data php bin/console glpi:plugin:activate esocss
sudo -u www-data php bin/console cache:clear
```

Depois acesse por um destes caminhos:

- **Configuração → ESO CSS for GLPI**;
- **Configuração → Plugins → ESO CSS for GLPI**.

## Atualização via Git

```bash
cd /var/www/html/plugins/esocss
sudo ./scripts/update.sh /var/www/html
```

Ou manualmente:

```bash
cd /var/www/html/plugins/esocss
git pull --ff-only

chown -R www-data:www-data /var/www/html/plugins/esocss

cd /var/www/html
sudo -u www-data php bin/console glpi:plugin:install --force esocss
sudo -u www-data php bin/console glpi:plugin:activate esocss
sudo -u www-data php bin/console cache:clear
```

### Atualização pela interface

Na página **Configurar → Plugins → ESO CSS for GLPI**:

1. clique em **Verificar versão**;
2. quando houver uma versão superior, clique em **Atualizar automaticamente**;
3. confirme a operação e recarregue a página após a conclusão.

Quando o componente de execução Git estiver disponível no GLPI, instalações feitas com `git clone` exigem uma pasta sem alterações locais e um `origin` apontando para este repositório. Nos demais ambientes, o plugin usa o ZIP da release e valida o checksum SHA-256 publicado junto ao arquivo.

O servidor web precisa ter permissão de escrita na pasta do plugin e na pasta `plugins`, necessária para a troca segura com backup temporário.

Para enviar imagens da página inicial, o usuário do servidor web também precisa escrever em `files/_plugins`. O plugin cria automaticamente o subdiretório `files/_plugins/esocss` e mantém as imagens nele para que não sejam perdidas durante atualizações.

### Cloudflare/WAF

Caso exista uma regra WAF para extensões PHP, adicione uma exceção restrita ao hostname do GLPI, a administradores autenticados e aos caminhos:

```text
/plugins/esocss/front/settings.php
/plugins/esocss/front/settings.form.php
/plugins/esocss/front/update.form.php
```

O arquivo `front/config.php` foi mantido somente para redirecionar favoritos antigos e não processa mais salvamentos.

A personalização anônima do login usa o gancho oficial `display_login`; ela não cria endpoint PHP público novo e não exige outra exceção no WAF.

## Interface de configuração

A página permite configurar:

- Ativação geral do tema.
- Modernização dos gráficos ECharts.
- Animação de cards.
- Cabeçalho escuro.
- Cor primária e hover.
- Fundo, texto, fundo em hover e texto em hover dos botões principais.
- Gradiente da sidebar.
- Cor do texto da sidebar.
- Gradiente do cabeçalho.
- Cor do texto do cabeçalho.
- Fundo geral e fundo dos cards.
- Cores de texto e bordas.
- Fundo, texto, hover e texto em hover dos menus.
- Raio dos cards.
- Intensidade da sombra.
- Raio e largura máxima das barras dos gráficos.
- Oito cores da paleta dos gráficos.
- CSS personalizado.
- Logo do menu lateral aberto e tamanho exibido.
- Logo quadrada do menu lateral recolhido, com reaproveitamento automático da logo principal quando vazia.
- Logo do cabeçalho horizontal/Helpdesk e ícone da aba do navegador.
- Ativação da página inicial personalizada.
- Título, subtítulo, imagem de fundo e logotipo do Helpdesk.
- Cor e intensidade da sobreposição, posição da imagem, altura do banner e tamanho do título.
- Exibição ou ocultação das ilustrações laterais nativas do GLPI.
- Ativação da tela de login personalizada.
- Título, subtítulo, imagem lateral ou de fundo e logotipo do login.
- Login centralizado, à esquerda ou à direita, com largura configurável para o painel de imagem.
- Cores de fundo, cartão, textos, botões, links e bordas da tela de login.
- Sobreposição da imagem, transparência, largura externa, largura do painel interno, cantos e altura do logotipo do login.
- Textos do botão Entra ID/SSO, “Lembrar de mim” e link para alternar ao formulário GLPI.
- Mensagem de boas-vindas, rótulos e exemplos dos campos, origem, botão Entrar, recuperação de senha, FAQ e rodapé.

## Como a configuração funciona

O plugin usa `Config::setConfigurationValues()` e `Config::getConfigurationValues()` no contexto:

```text
plugin:esocss
```

Isso evita criar tabelas próprias apenas para preferências visuais.

O arquivo `public/js/eso-theme.js` consulta:

```text
/plugins/esocss/front/config-data.php
```

A resposta JSON é usada para:

1. definir variáveis CSS no `:root`;
2. habilitar/desabilitar classes do tema;
3. inserir o CSS personalizado;
4. localizar instâncias ECharts em `.g-chart .chart`;
5. reaplicar a paleta e estilos após widgets carregados por AJAX.
6. aplicar o banner, logotipo, título e subtítulo na página inicial Helpdesk;
7. aplicar os logotipos do menu lateral, cabeçalho e o ícone da aba do navegador.

Na tela anônima, o plugin não consulta esse endpoint. O gancho `display_login` entrega somente as preferências de identidade visual sanitizadas; o CSS personalizado e as demais opções administrativas não são expostos. O CSS e o JavaScript do login são registrados pelos ganchos anônimos próprios do GLPI 11.

## Logotipos e identidade global

Na seção **Logotipos e identidade global** é possível enviar imagens diferentes para o menu lateral aberto, o menu lateral recolhido, o cabeçalho horizontal/Helpdesk e a aba do navegador. As imagens são aplicadas sobre a estrutura oficial do GLPI 11, sem substituir arquivos internos.

Uma imagem horizontal transparente é indicada para o menu aberto e o cabeçalho. Para o menu recolhido e o favicon, prefira um símbolo quadrado. Quando a imagem compacta ficar vazia, a logo lateral principal é reutilizada automaticamente. Se uma posição não tiver imagem configurada, o GLPI mantém a identidade nativa.

Na página inicial do Helpdesk, a logo definida na seção específica da página inicial tem prioridade sobre a logo global do cabeçalho. O mesmo vale para a logo específica da tela de login.

## Página inicial Helpdesk

Na seção **Página inicial do Helpdesk** da interface administrativa é possível enviar uma imagem de fundo e um logotipo, ajustar a sobreposição e controlar título, subtítulo, altura do banner e enquadramento da imagem.

Formatos aceitos: JPG, PNG e WebP, com até 5 MB e no máximo 40 milhões de pixels. Os arquivos são validados pelo conteúdo real, recebem nomes gerenciados pelo plugin e nunca são executados como PHP.

As ilustrações esquerda e direita continuam sendo configuradas pelo recurso nativo do GLPI 11 na entidade. O plugin oferece apenas a opção de ocultá-las quando for utilizada uma fotografia de fundo.

## Tela de login e login único

Na seção **Tela de login**, o campo **Estilo da tela de login** permite escolher entre o modelo personalizado já existente, **Vidro central** e **Portal lateral**. Os dois novos modelos usam a fotografia em tela cheia com sobreposição e um único painel translúcido; o Portal lateral também exibe o título e o subtítulo no lado livre da imagem. Logo, foto, cores, transparência, textos e posição continuam configuráveis.

No estilo personalizado atual também é possível usar a imagem em um painel separado, sem colocá-la atrás do formulário. O login pode ficar centralizado, à esquerda ou à direita; nesta última opção, a imagem ocupa o lado esquerdo em um layout semelhante ao ST Login. O plugin reconhece a estrutura `.singlesignon-*` usada pelo login único/Entra ID.

Em telas menores que 900 px, o painel de imagem é ocultado automaticamente e o formulário volta ao centro para preservar a leitura e o acesso aos campos.

O ESO CSS apenas estiliza os controles que o GLPI e os plugins de autenticação já renderizam. O botão do Entra ID, o formulário nativo, os endereços de retorno, os tokens e as regras de sessão não são criados nem modificados por este plugin.

Todos os textos visíveis podem ser deixados vazios para preservar a tradução e as mensagens originais. Quando preenchidos, somente a legenda apresentada ao usuário é substituída; links, campos, botões e ações continuam sendo os elementos originais do provedor.

Se o título for deixado vazio, o texto fornecido pelo GLPI ou pelo plugin de login único é preservado. A opção vem desativada após a atualização; configure a prévia, marque **Ativar personalização da tela de login** e salve quando estiver pronto.

As imagens usam a mesma validação segura da página inicial e permanecem em `files/_plugins/esocss` durante atualizações automáticas.

## ECharts no GLPI 11

O Dashboard do GLPI 11 usa Apache ECharts e inicializa os gráficos aproximadamente assim:

```javascript
const myChart = echarts.init(target[0]);
myChart.setOption(options);
```

O plugin utiliza `echarts.getInstanceByDom()` para recuperar a instância já criada, sem substituir o mecanismo nativo do GLPI.

## Diagnóstico

### Confirmar o plugin

```bash
cd /var/www/html
sudo -u www-data php bin/console glpi:plugin:list | grep esocss
```

Esperado:

```text
esocss | ESO CSS for GLPI | 1.9.0 | Habilitado
```

### Confirmar CSS

```bash
curl -I https://SEU_GLPI/plugins/esocss/css/eso-modern.css
```

Esperado:

```text
HTTP/1.1 200 OK
Content-Type: text/css
```

### Confirmar JavaScript

```bash
curl -I https://SEU_GLPI/plugins/esocss/js/eso-theme.js
```

Esperado:

```text
HTTP/1.1 200 OK
Content-Type: application/javascript
```

### Confirmar endpoint de configuração

Com uma sessão autenticada, no navegador:

```text
https://SEU_GLPI/plugins/esocss/front/config-data.php
```

Deve retornar JSON com as configurações do tema.

### Cache do GLPI

```bash
cd /var/www/html
sudo -u www-data php bin/console cache:clear
```

Se necessário, faça `Ctrl + Shift + R` no navegador ou marque **Disable cache** no DevTools durante o teste.

## Desenvolvimento

Validação rápida de sintaxe PHP:

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

O repositório inclui GitHub Actions para validar sintaxe PHP, JavaScript e Bash, além de testar a sanitização das configurações, em todo `push` e `pull_request`.

## Segurança

A página de configuração exige direito de atualização da configuração global do GLPI (`config`, `UPDATE`).

Somente administradores com esse direito devem ter acesso ao campo **CSS personalizado**.

A atualização automática aceita apenas releases estáveis do repositório `lexone/ESO-CSS-for-GLPI`. Pacotes ZIP são instalados somente quando o checksum SHA-256 confere e o conteúdo declara a mesma versão da release.

Uploads de identidade visual aceitam apenas imagens raster válidas, limitam tamanho e dimensões e são armazenados na pasta de documentos do GLPI, fora da raiz pública do plugin.

## Licença

GPLv3.

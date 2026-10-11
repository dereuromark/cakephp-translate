import { defineConfig } from 'vitepress'
export default defineConfig({
  title: 'CakePHP Translate',
  description: 'Manage gettext catalogs and database translations in CakePHP.',
  base: '/cakephp-translate/', cleanUrls: true, lastUpdated: true,
  srcExclude: ['README.md'],
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-translate/' },
  head: [['link', { rel: 'icon', href: '/cakephp-translate/favicon.svg', type: 'image/svg+xml' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/configuration' },
    ],
    sidebar: [
      { text: 'Guide', items: [
        { text: 'Installation', link: '/guide/installation' },
        { text: 'Getting started', link: '/guide/getting-started' },
        { text: 'Translation strategies', link: '/guide/strategies' },
        { text: 'Translating', link: '/Translating' },
        { text: 'TranslateBehavior entries', link: '/I18nEntries' },
      ] },
      { text: 'Reference', items: [
        { text: 'Configuration', link: '/reference/configuration' },
        { text: 'Upgrading to CakePHP 6', link: '/reference/upgrading' },
        { text: 'CLI commands', link: '/reference/cli' },
        { text: 'Translator engines', link: '/TranslatorEngines' },
        { text: 'Layout', link: '/contribute/layout' },
        { text: 'Contributing', link: '/Contributing' },
      ] },
    ],
    search: { provider: 'local' },
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-translate' }],
    editLink: { pattern: 'https://github.com/dereuromark/cakephp-translate/edit/cake6/docs/:path' },
    footer: { message: 'Released under the MIT License.', copyright: 'Copyright Mark Scherer' },
  },
})

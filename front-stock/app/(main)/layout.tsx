import ResponsiveLayout from "../../components/core/layouts/LayoutConfirmation";

// Métadonnées pour le SEO
export const metadata = {
  title: {
    template: "%s | Solution Stock",
    default: "Solution Stock - Gestion intelligente des stocks",
  },
  description:
    "Solution Stock : logiciel de gestion des stocks professionnel. Optimisez votre inventaire, réduisez les coûts et améliorez votre rentabilité.",
  keywords:
    "gestion stock, inventaire, logiciel stock, Solution Stock, gestion inventaire, optimisation stock",
  authors: [{ name: "Solution Stock Team" }],
  creator: "Solution Stock",
  publisher: "Solution Stock",

  // Open Graph pour les réseaux sociaux
  openGraph: {
    title: "Solution Stock - Gestion intelligente des stocks",
    description:
      "Optimisez votre inventaire avec Solution Stock. Réduisez les coûts et améliorez votre rentabilité.",
    url: "https://solutionstock.com",
    siteName: "Solution Stock",
    locale: "fr_FR",
    type: "website",
  },

  // Twitter Card
  twitter: {
    card: "summary_large_image",
    title: "Solution Stock - Gestion intelligente des stocks",
    description:
      "Optimisez votre inventaire avec Solution Stock. Réduisez les coûts et améliorez votre rentabilité.",
    creator: "@solutionstock",
  },

  // Robots
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      "max-video-preview": -1,
      "max-image-preview": "large",
      "max-snippet": -1,
    },
  },

  // Autres métadonnées importantes
  category: "Logiciel de gestion",
  classification: "Gestion d'entreprise",
};

function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="fr">
      <head>
        {/* DNS Prefetch pour optimiser les performances */}
        <link rel="dns-prefetch" href="//fonts.googleapis.com" />
        <link rel="dns-prefetch" href="//www.google-analytics.com" />

        {/* Preconnect pour les ressources critiques */}
        <link
          rel="preconnect"
          href="https://fonts.gstatic.com"
          crossOrigin="anonymous"
        />

        {/* Favicon et icônes */}
        <link rel="icon" href="/favicon.ico" />
        <link
          rel="apple-touch-icon"
          sizes="180x180"
          href="/apple-touch-icon.png"
        />
        <link
          rel="icon"
          type="image/png"
          sizes="32x32"
          href="/favicon-32x32.png"
        />
        <link
          rel="icon"
          type="image/png"
          sizes="16x16"
          href="/favicon-16x16.png"
        />
        <link rel="manifest" href="/site.webmanifest" />

        {/* Canonical URL - à adapter selon votre domaine */}
        <link rel="canonical" href="https://solutionstock.com" />

        {/* Données structurées JSON-LD */}
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{
            __html: JSON.stringify({
              "@context": "https://schema.org",
              "@type": "SoftwareApplication",
              name: "Solution Stock",
              description:
                "Logiciel de gestion des stocks professionnel pour optimiser votre inventaire",
              applicationCategory: "BusinessApplication",
              operatingSystem: "Web Browser",
              offers: {
                "@type": "Offer",
                category: "Logiciel de gestion",
              },
              creator: {
                "@type": "Organization",
                name: "Solution Stock",
              },
            }),
          }}
        />
      </head>

      <body>
        <ResponsiveLayout>{children}</ResponsiveLayout>
      </body>
    </html>
  );
}

export default RootLayout;

import ProductCompleteForm from "../../../../components/forms/product-complete-form";

// Métadonnées SEO pour la page d'enregistrement de produit
export const metadata = {
  title: "Enregistrer un nouveau produit | Solution Stock",
  description:
    "Ajoutez facilement de nouveaux produits à votre inventaire avec Solution Stock. Enregistrement rapide avec code-barres, gestion des stocks et suivi automatique.",
  keywords:
    "enregistrer produit, ajouter produit stock, nouveau produit inventaire, code-barres produit, gestion produit, Solution Stock",

  // Open Graph
  openGraph: {
    title: "Enregistrer un nouveau produit | Solution Stock",
    description:
      "Ajoutez facilement de nouveaux produits à votre inventaire. Interface intuitive pour l'enregistrement rapide de vos produits.",
    url: "https://solutionstock.com/products/register",
    type: "website",
    locale: "fr_FR",
  },

  // Twitter Card
  twitter: {
    card: "summary",
    title: "Enregistrer un nouveau produit | Solution Stock",
    description:
      "Ajoutez facilement de nouveaux produits à votre inventaire avec Solution Stock.",
  },

  // Robots - page interne, indexable mais pas prioritaire
  robots: {
    index: true,
    follow: true,
    noarchive: true, // Évite la mise en cache de cette page formulaire
  },

  // Autres métadonnées
  category: "Gestion de produits",
  classification: "Formulaire d'enregistrement",
};

function ProductRegistrationPage() {
  return (
    <>
      {/* Données structurées pour le formulaire d'enregistrement */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify({
            "@context": "https://schema.org",
            "@type": "WebPage",
            name: "Enregistrer un nouveau produit",
            description:
              "Formulaire d'enregistrement de nouveaux produits dans l'inventaire",
            url: "https://solutionstock.com/products/register",
            isPartOf: {
              "@type": "WebSite",
              name: "Solution Stock",
              url: "https://solutionstock.com",
            },
            breadcrumb: {
              "@type": "BreadcrumbList",
              itemListElement: [
                {
                  "@type": "ListItem",
                  position: 1,
                  name: "Accueil",
                  item: "https://solutionstock.com",
                },
                {
                  "@type": "ListItem",
                  position: 2,
                  name: "Produits",
                  item: "https://solutionstock.com/products",
                },
                {
                  "@type": "ListItem",
                  position: 3,
                  name: "Enregistrer un produit",
                  item: "https://solutionstock.com/products/register",
                },
              ],
            },
            potentialAction: {
              "@type": "CreateAction",
              name: "Enregistrer un produit",
              description: "Ajouter un nouveau produit à l'inventaire",
            },
          }),
        }}
      />

      <div className="max-w-7xl mx-auto ">
        {/* Structure sémantique pour le SEO */}
        <div className="border border-gray-300 bg-white p-4 rounded-md">
          <ProductCompleteForm />
        </div>
      </div>
    </>
  );
}

export default ProductRegistrationPage;

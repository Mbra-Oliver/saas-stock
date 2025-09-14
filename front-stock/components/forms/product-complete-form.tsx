"use client";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  AlertTriangle,
  Camera,
  Check,
  ChevronLeft,
  ChevronRight,
  DollarSign,
  Loader2,
  Package,
  Star,
  Upload,
  X,
} from "lucide-react";
import React, { useState } from "react";
import { useForm } from "react-hook-form";
import * as z from "zod";

// Importation des composants shadcn/ui
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormDescription,
  FormErrorMessage,
  FormField,
  FormItem,
  FormLabel,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";

// Types TypeScript
interface ProductImage {
  id: number;
  file: File;
  url: string;
  name: string;
}

interface ProductDimensions {
  length?: number;
  width?: number;
  height?: number;
}

interface ProductFormData {
  name: string;
  description?: string;
  sku: string;
  barcode?: string;
  category?: string;
  brand?: string;
  price: number;
  cost?: number;
  quantity: number;
  minStock: number;
  maxStock?: number;
  location?: string;
  supplier?: string;
  unit: string;
  weight?: number;
  dimensions?: ProductDimensions;
}

interface ProductCreatePayload extends ProductFormData {
  images: Array<{
    file: File;
    isPrimary: boolean;
  }>;
}

interface UseCreateProductReturn {
  mutate: (data: ProductCreatePayload) => Promise<void>;
  isLoading: boolean;
}

type CategoryOption =
  | "electronique"
  | "vetements"
  | "maison"
  | "sport"
  | "beaute";
type UnitOption = "pièce" | "kg" | "litre" | "mètre" | "boîte" | "pack";

// Schéma de validation Zod corrigé
const productSchema = z
  .object({
    name: z
      .string()
      .min(1, "Le nom est requis")
      .min(3, "Le nom doit contenir au moins 3 caractères"),
    description: z.string().optional(),
    sku: z
      .string()
      .min(1, "Le SKU est requis")
      .regex(
        /^[A-Za-z0-9\-_]+$/,
        "Le SKU ne peut contenir que des lettres, chiffres, tirets et underscores"
      ),
    barcode: z.string().optional(),
    category: z.string().optional(),
    brand: z.string().optional(),
    price: z.number().positive("Le prix doit être positif"),
    cost: z.number().optional(),
    quantity: z.number().min(0, "La quantité ne peut pas être négative"),
    minStock: z.number().min(0, "Le stock minimum ne peut pas être négatif"),
    maxStock: z.number().optional(),
    location: z.string().optional(),
    supplier: z.string().optional(),
    unit: z.string().default("pièce"),
    weight: z.number().optional(),
    dimensions: z
      .object({
        length: z.number().optional(),
        width: z.number().optional(),
        height: z.number().optional(),
      })
      .optional(),
  })
  .refine((data) => !data.maxStock || data.maxStock >= data.minStock, {
    message: "Le stock maximum doit être supérieur au stock minimum",
    path: ["maxStock"],
  });

type ProductFormSchema = z.infer<typeof productSchema>;

// Simulation de React Query avec typage
const useCreateProduct = (): UseCreateProductReturn => {
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const mutate = async (productData: ProductCreatePayload): Promise<void> => {
    setIsLoading(true);
    try {
      // Simulation d'API call
      await new Promise<void>((resolve) => setTimeout(resolve, 2000));
      console.log("Produit créé:", productData);
    } catch (error) {
      console.error("Erreur lors de la création:", error);
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  return { mutate, isLoading };
};

const ProductCompleteForm: React.FC = () => {
  const { mutate: createProduct, isLoading } = useCreateProduct();
  const [images, setImages] = useState<ProductImage[]>([]);
  const [primaryImageIndex, setPrimaryImageIndex] = useState<number>(0);
  const [currentStep, setCurrentStep] = useState<number>(0);

  // Configuration React Hook Form avec Zod et typage strict
  const form = useForm({
    resolver: zodResolver(productSchema),
    defaultValues: {
      name: "",
      description: "",
      sku: "",
      barcode: "",
      category: "",
      brand: "",
      price: 0,
      cost: 0,
      quantity: 0,
      minStock: 0,
      maxStock: 0,
      location: "",
      supplier: "",
      unit: "pièce",
      weight: 0,
      dimensions: {
        length: 0,
        width: 0,
        height: 0,
      },
    },
  });

  // Configuration des étapes
  const steps = [
    {
      title: "Images",
      icon: Camera,
      fields: [], // Les images sont gérées séparément
    },
    {
      title: "Informations de base",
      icon: Package,
      fields: [
        "name",
        "sku",
        "barcode",
        "category",
        "brand",
        "unit",
        "description",
      ] as const,
    },
    {
      title: "Prix et coûts",
      icon: DollarSign,
      fields: ["price", "cost"] as const,
    },
    {
      title: "Stock",
      icon: AlertTriangle,
      fields: ["quantity", "minStock", "maxStock"] as const,
    },
    {
      title: "Caractéristiques",
      icon: Package,
      fields: ["weight", "location", "dimensions", "supplier"] as const,
    },
  ];

  // Gestion des images avec typage
  const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>): void => {
    const files = Array.from(e.target.files || []);

    files.forEach((file: File) => {
      if (file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (event: ProgressEvent<FileReader>): void => {
          if (event.target?.result) {
            setImages((prev) => [
              ...prev,
              {
                id: Date.now() + Math.random(),
                file,
                url: event?.target?.result as string,
                name: file.name,
              },
            ]);
          }
        };
        reader.readAsDataURL(file);
      }
    });
  };

  const removeImage = (imageId: number): void => {
    setImages((prev) => {
      const newImages = prev.filter((img) => img.id !== imageId);
      if (primaryImageIndex >= newImages.length && newImages.length > 0) {
        setPrimaryImageIndex(newImages.length - 1);
      } else if (newImages.length === 0) {
        setPrimaryImageIndex(0);
      }
      return newImages;
    });
  };

  const setPrimaryImage = (index: number): void => {
    setPrimaryImageIndex(index);
  };

  // Navigation entre les étapes
  const nextStep = async (): Promise<void> => {
    const currentStepFields = steps[currentStep].fields;

    if (currentStepFields.length > 0) {
      const isValid = await form.trigger(currentStepFields);
      if (!isValid) return;
    }

    if (currentStep < steps.length - 1) {
      setCurrentStep(currentStep + 1);
    }
  };

  const prevStep = (): void => {
    if (currentStep > 0) {
      setCurrentStep(currentStep - 1);
    }
  };

  // Soumission du formulaire avec typage strict
  const onSubmit = async (data: ProductFormSchema): Promise<void> => {
    try {
      const productData: ProductCreatePayload = {
        ...data,
        images: images.map((img: ProductImage, index: number) => ({
          file: img.file,
          isPrimary: index === primaryImageIndex,
        })),
      };

      await createProduct(productData);
    } catch (error) {
      console.error("Erreur lors de la soumission:", error);
    }
  };

  // Options typées pour les selects
  const categoryOptions: Array<{ value: CategoryOption; label: string }> = [
    { value: "electronique", label: "Électronique" },
    { value: "vetements", label: "Vêtements" },
    { value: "maison", label: "Maison & Jardin" },
    { value: "sport", label: "Sport" },
    { value: "beaute", label: "Beauté" },
  ];

  const unitOptions: Array<{ value: UnitOption; label: string }> = [
    { value: "pièce", label: "Pièce" },
    { value: "kg", label: "Kilogramme" },
    { value: "litre", label: "Litre" },
    { value: "mètre", label: "Mètre" },
    { value: "boîte", label: "Boîte" },
    { value: "pack", label: "Pack" },
  ];

  // Rendu des étapes
  const renderStepContent = () => {
    switch (currentStep) {
      case 0: // Images
        return (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium flex items-center gap-2">
                <Camera className="w-5 h-5" />
                Images du produit
              </h3>
              <p className="text-sm text-muted-foreground">
                Ajoutez des images de votre produit
              </p>
            </div>

            {/* Upload zone */}
            <div className="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 text-center hover:border-primary/50 transition-colors">
              <input
                type="file"
                multiple
                accept="image/*"
                onChange={handleImageUpload}
                className="hidden"
                id="image-upload"
              />
              <Label htmlFor="image-upload" className="cursor-pointer">
                <Upload className="w-8 h-8 text-muted-foreground mx-auto mb-2" />
                <p className="text-muted-foreground">
                  Cliquez pour ajouter des images ou glissez-déposez
                </p>
                <p className="text-sm text-muted-foreground/70 mt-1">
                  PNG, JPG jusqu'à 10MB
                </p>
              </Label>
            </div>

            {/* Aperçu des images */}
            {images.length > 0 && (
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                {images.map((image: ProductImage, index: number) => (
                  <div key={image.id} className="relative group">
                    <img
                      src={image.url}
                      alt={`Produit ${index + 1}`}
                      className={`w-full h-24 object-cover rounded-lg border-2 ${
                        index === primaryImageIndex
                          ? "border-primary"
                          : "border-border"
                      }`}
                    />

                    {/* Badge image principale */}
                    {index === primaryImageIndex && (
                      <Badge className="absolute top-1 left-1 text-xs">
                        Principal
                      </Badge>
                    )}

                    {/* Actions */}
                    <div className="absolute top-1 right-1 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      {index !== primaryImageIndex && (
                        <Button
                          type="button"
                          size="sm"
                          variant="secondary"
                          onClick={() => setPrimaryImage(index)}
                          className="h-6 w-6 p-0"
                        >
                          <Star className="w-3 h-3" />
                        </Button>
                      )}

                      <Button
                        type="button"
                        size="sm"
                        variant={"secondary"}
                        onClick={() => removeImage(image.id)}
                        className="h-6 w-6 p-0"
                      >
                        <X className="w-3 h-3" />
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        );

      case 1: // Informations de base
        return (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">Informations de base</h3>
              <p className="text-sm text-muted-foreground">
                Renseignez les informations principales du produit
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="name"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Nom du produit *</FormLabel>
                    <FormControl>
                      <Input placeholder="Ex: iPhone 15 Pro" {...field} />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="sku"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>SKU (Code produit) *</FormLabel>
                    <FormControl>
                      <Input placeholder="Ex: IPH15PRO-256" {...field} />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="barcode"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Code-barres</FormLabel>
                    <FormControl>
                      <Input placeholder="Ex: 1234567890123" {...field} />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="category"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Catégorie</FormLabel>
                    <Select
                      onValueChange={field.onChange}
                      defaultValue={field.value}
                    >
                      <FormControl>
                        <SelectTrigger>
                          <SelectValue placeholder="Sélectionner une catégorie" />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {categoryOptions.map((option) => (
                          <SelectItem key={option.value} value={option.value}>
                            {option.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="brand"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Marque</FormLabel>
                    <FormControl>
                      <Input placeholder="Ex: Apple" {...field} />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="unit"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Unité</FormLabel>
                    <Select
                      onValueChange={field.onChange}
                      defaultValue={field.value}
                    >
                      <FormControl>
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                      </FormControl>
                      <SelectContent>
                        {unitOptions.map((option) => (
                          <SelectItem key={option.value} value={option.value}>
                            {option.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />
            </div>

            <FormField
              control={form.control}
              name="description"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Description</FormLabel>
                  <FormControl>
                    <Textarea
                      placeholder="Description détaillée du produit..."
                      className="resize-none"
                      {...field}
                    />
                  </FormControl>
                  <FormErrorMessage />
                </FormItem>
              )}
            />
          </div>
        );

      case 2: // Prix et coûts
        return (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium flex items-center gap-2">
                <DollarSign className="w-5 h-5" />
                Prix et coûts
              </h3>
              <p className="text-sm text-muted-foreground">
                Définissez les prix de vente et d'achat
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="price"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Prix de vente (€) *</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        {...field}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                          field.onChange(parseFloat(e.target.value) || 0)
                        }
                      />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="cost"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Prix d'achat (€)</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        {...field}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                          field.onChange(parseFloat(e.target.value) || 0)
                        }
                      />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />
            </div>
          </div>
        );

      case 3: // Stock
        return (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium flex items-center gap-2">
                <AlertTriangle className="w-5 h-5" />
                Stock et alertes
              </h3>
              <p className="text-sm text-muted-foreground">
                Gérez les quantités et les alertes de stock
              </p>
            </div>

            <Alert>
              <AlertTriangle className="h-4 w-4" />
              <AlertDescription>
                Le stock minimum déclenche une alerte lorsque la quantité est
                atteinte.
              </AlertDescription>
            </Alert>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <FormField
                control={form.control}
                name="quantity"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Quantité actuelle *</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        placeholder="0"
                        {...field}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                          field.onChange(parseInt(e.target.value) || 0)
                        }
                      />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="minStock"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Stock minimum (alerte) *</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        placeholder="5"
                        {...field}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                          field.onChange(parseInt(e.target.value) || 0)
                        }
                      />
                    </FormControl>
                    <FormDescription>
                      Déclenche une alerte si atteint
                    </FormDescription>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="maxStock"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Stock maximum</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        placeholder="100"
                        {...field}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                          field.onChange(parseInt(e.target.value) || 0)
                        }
                      />
                    </FormControl>
                    <FormDescription>
                      Capacité maximale de stockage
                    </FormDescription>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />
            </div>
          </div>
        );

      case 4: // Caractéristiques
        return (
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-medium">
                Caractéristiques physiques
              </h3>
              <p className="text-sm text-muted-foreground">
                Informations sur les dimensions, poids et fournisseur
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <FormField
                control={form.control}
                name="weight"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Poids (kg)</FormLabel>
                    <FormControl>
                      <Input
                        type="number"
                        step="0.01"
                        placeholder="0.00"
                        {...field}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                          field.onChange(parseFloat(e.target.value) || 0)
                        }
                      />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={form.control}
                name="location"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Emplacement</FormLabel>
                    <FormControl>
                      <Input placeholder="Ex: Allée A, Étagère 3" {...field} />
                    </FormControl>
                    <FormErrorMessage />
                  </FormItem>
                )}
              />
            </div>

            <div>
              <Label className="text-sm font-medium">Dimensions (cm)</Label>
              <div className="grid grid-cols-3 gap-2 mt-2">
                <FormField
                  control={form.control}
                  name="dimensions.length"
                  render={({ field }) => (
                    <FormItem>
                      <FormControl>
                        <Input
                          type="number"
                          step="0.1"
                          placeholder="Longueur"
                          {...field}
                          onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                            field.onChange(parseFloat(e.target.value) || 0)
                          }
                        />
                      </FormControl>
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="dimensions.width"
                  render={({ field }) => (
                    <FormItem>
                      <FormControl>
                        <Input
                          type="number"
                          step="0.1"
                          placeholder="Largeur"
                          {...field}
                          onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                            field.onChange(parseFloat(e.target.value) || 0)
                          }
                        />
                      </FormControl>
                    </FormItem>
                  )}
                />
                <FormField
                  control={form.control}
                  name="dimensions.height"
                  render={({ field }) => (
                    <FormItem>
                      <FormControl>
                        <Input
                          type="number"
                          step="0.1"
                          placeholder="Hauteur"
                          {...field}
                          onChange={(e: React.ChangeEvent<HTMLInputElement>) =>
                            field.onChange(parseFloat(e.target.value) || 0)
                          }
                        />
                      </FormControl>
                    </FormItem>
                  )}
                />
              </div>
            </div>

            <FormField
              control={form.control}
              name="supplier"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Nom du fournisseur</FormLabel>
                  <FormControl>
                    <Input placeholder="Ex: Distributeur ABC" {...field} />
                  </FormControl>
                  <FormErrorMessage />
                </FormItem>
              )}
            />
          </div>
        );

      default:
        return null;
    }
  };

  return (
    <div className=" p-6">
      <div className="mb-8">
        <div className="flex items-center gap-2 mb-2">
          <Package className="w-6 h-6 text-blue-600" />
          <h1 className="text-2xl font-bold">Nouveau Produit</h1>
        </div>
        <p className="text-muted-foreground">
          Renseignez toutes les informations du produit
        </p>
      </div>

      {/* Stepper */}
      <div className="flex items-center justify-between mb-8">
        {steps.map((step, index) => {
          const Icon = step.icon;
          return (
            <div
              key={index}
              className={`flex items-center ${
                index < steps.length - 1 ? "flex-1" : ""
              }`}
            >
              <div className="flex items-center">
                <div
                  className={`w-10 h-10 aspect-square rounded-full flex items-center justify-center text-sm font-medium ${
                    index <= currentStep
                      ? "bg-primary text-primary-foreground"
                      : "bg-muted text-muted-foreground"
                  }`}
                >
                  {index < currentStep ? (
                    <Check className="w-5 h-5" />
                  ) : (
                    <Icon className="w-5 h-5" />
                  )}
                </div>
                <div className="ml-2 hidden sm:block">
                  <p
                    className={`text-sm font-medium ${
                      index <= currentStep
                        ? "text-foreground"
                        : "text-muted-foreground"
                    }`}
                  >
                    {step.title}
                  </p>
                </div>
              </div>
              {index < steps.length - 1 && (
                <div
                  className={`flex-1 h-px ml-4 ${
                    index < currentStep ? "bg-primary" : "bg-muted"
                  }`}
                />
              )}
            </div>
          );
        })}
      </div>

      <Form {...form}>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-8">
          <div className="bg-card rounded-lg border p-6">
            {renderStepContent()}
          </div>

          {/* Navigation buttons */}
          <div className="flex justify-between">
            <Button
              type="button"
              variant="outline"
              onClick={prevStep}
              disabled={currentStep === 0}
              className="flex items-center gap-2"
            >
              <ChevronLeft className="w-4 h-4" />
              Précédent
            </Button>

            {currentStep < steps.length - 1 ? (
              <Button
                type="button"
                onClick={nextStep}
                className="flex items-center gap-2"
              >
                Suivant
                <ChevronRight className="w-4 h-4" />
              </Button>
            ) : (
              <Button
                type="submit"
                disabled={isLoading}
                className="flex items-center gap-2"
              >
                {isLoading ? (
                  <>
                    <Loader2 className="w-4 h-4 animate-spin" />
                    Création en cours...
                  </>
                ) : (
                  <>
                    <Check className="w-4 h-4" />
                    Créer le produit
                  </>
                )}
              </Button>
            )}
          </div>
        </form>
      </Form>
    </div>
  );
};

export default ProductCompleteForm;

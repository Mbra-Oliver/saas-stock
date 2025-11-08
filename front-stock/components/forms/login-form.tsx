"use client";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormErrorMessage,
  FormField,
  FormItem,
  FormLabel,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { PasswordInput } from "@/components/ui/password-input";

import { zodResolver } from "@hookform/resolvers/zod";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { z } from "zod";

const loginFormValidationSchema = z.object({
  email: z.string().email(),
  password: z.string(),
});
export const LoginForm = () => {
  const router = useRouter();
  // const queryClient = useQueryClient();
  const form = useForm<z.infer<typeof loginFormValidationSchema>>({
    resolver: zodResolver(loginFormValidationSchema),
    mode: "onSubmit",
    reValidateMode: "onSubmit",
    defaultValues: {
      email: "",
      password: "",
    },
  });

  const onSubmit = async (
    values: z.infer<typeof loginFormValidationSchema>
  ) => {
    try {
    } catch (err: any) {
      form.setError("root.serverCatch", {
        message: err.message ?? "Error",
      });
    }
  };
  return (
    <Form {...form}>
      <form onSubmit={form.handleSubmit(onSubmit)} className="w-md max-w-full">
        <div className="text-3xl font-medium text-gray-800 mb-8">Connexion</div>
        {form.formState.errors.root?.serverCatch.message && (
          <div className="mb-5">
            <Alert variant="outline" colorScheme="red">
              <AlertDescription>
                {form.formState.errors.root.serverCatch.message}
              </AlertDescription>
            </Alert>
          </div>
        )}
        <div className="mb-7">
          <FormField
            control={form.control}
            name="email"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Adresse mail</FormLabel>
                <FormControl>
                  <Input placeholder="Entrez votre adresse mail" {...field} />
                </FormControl>
                <FormErrorMessage />
              </FormItem>
            )}
          />
        </div>
        <FormField
          control={form.control}
          name="password"
          render={({ field }) => (
            <FormItem>
              <FormLabel>Mot de passe</FormLabel>
              <FormControl>
                <PasswordInput
                  placeholder="Entrez votre mot de passe"
                  {...field}
                />
              </FormControl>
              <FormErrorMessage />
            </FormItem>
          )}
        />

        <div className="flex justify-end mt-1">
          <Link
            href="/password-reset"
            type="button"
            className="cursor-pointer border-b transition-colors text-sm border-transparent hover:border-blue-500 text-gray-800 hover:text-blue-500"
          >
            Mot de passe oublié
          </Link>
        </div>

        <div className="mt-4">
          <Button
            type="submit"
            fullWidth
            isLoading={
              form.formState.isSubmitting || form.formState.isSubmitSuccessful
            }
          >
            Se connecter
          </Button>
        </div>

        <div className="text-sm flex mt-2">
          <p className="text-gray-600">Vous n'avez pas de compte ? &nbsp;</p>
          <Link
            href="/register"
            className="text-blue-500 cursor-pointer border-b border-transparent hover:border-blue-500"
          >
            Inscrivez vous
          </Link>
        </div>
      </form>
    </Form>
  );
};

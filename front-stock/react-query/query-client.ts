import { QueryClient } from "@tanstack/react-query";

// Créer une instance de QueryClient qui peut être utilisée en dehors du Provider
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 0,
      refetchOnWindowFocus: false,
    },
  },
});

"use client";
import { NiceModalProvider } from "@/components/ui/nice-modal";
import React from "react";
import { Toaster } from "../components/ui/sonner";
import { ReactQueryProvider } from "../react-query/react-query-provide";

function ClientLayout({ children }: { children: React.ReactNode }) {
  return (
    <ReactQueryProvider>
      <NiceModalProvider>
        {children}
        <Toaster />
      </NiceModalProvider>
    </ReactQueryProvider>
  );
}

export default ClientLayout;

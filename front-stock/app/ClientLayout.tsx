"use client";
import React from "react";
import { ReactQueryProvider } from "../react-query/react-query-provide";
function ClientLayout({ children }: { children: React.ReactNode }) {
  return <ReactQueryProvider>{children}</ReactQueryProvider>;
}

export default ClientLayout;

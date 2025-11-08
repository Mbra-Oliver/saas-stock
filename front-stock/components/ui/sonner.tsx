"use client";
// import { useTheme } from "next-themes";
import { Toaster as Sonner, ToasterProps } from "sonner";

const Toaster = ({ ...props }: ToasterProps) => {
  // const { theme = "system" } = useTheme();

  return (
    <Sonner
      // theme={theme as ToasterProps["theme"]}
      theme="system"
      className="toaster group"
      toastOptions={{
        unstyled: true,

        classNames: {
          toast:
            "flex gap-x-3 items-center pl-2.5 pr-4 py-3 rounded group-[.toaster]:border-border group-[.toaster]:shadow-lg",
          title: "text-sm",
          description: "text-xs",
          actionButton: "bg-zinc-400",
          cancelButton: "bg-orange-400",
          closeButton: "bg-lime-400",
          success: "bg-green-500 text-white",
          error: "bg-red-500 text-white",
          info: "bg-blue-500 text-white",
          warning: "bg-yellow-500 text-white",
        },
      }}
      {...props}
    />
  );
};

export { Toaster };

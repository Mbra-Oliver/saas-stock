import * as React from "react";

// import { cn } from "@/lib/utils";
import { cn } from "@/lib/utils";
import { cva, VariantProps } from "class-variance-authority";

const inputStyle = cva(
  [
    "file:text-foreground placeholder:text-gray-400 text-sm selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-gray-300 flex w-full min-w-0 border bg-white px-3 py-1 shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
    "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
    "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
    "input",
  ],
  {
    variants: {
      size: {
        xs: "h-7 rounded",
        sm: "h-8 rounded",
        md: "h-10 rounded-[6px]",
        lg: "h-11 rounded",
        xl: "h-12 rounded",
      },
    },
    defaultVariants: {
      size: "md",
    },
  }
);

interface InputProps
  extends Omit<React.ComponentProps<"input">, "size">,
    VariantProps<typeof inputStyle> {}

function Input({ className, type, size, ...props }: InputProps) {
  return (
    <input
      type={type}
      data-slot="input"
      className={cn(inputStyle({ size }), className)}
      {...props}
    />
  );
}

export { Input, type InputProps };

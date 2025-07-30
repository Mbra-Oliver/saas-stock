"use client";
import { Slot } from "@radix-ui/react-slot";
import { cva, type VariantProps } from "class-variance-authority";
import * as React from "react";

import { cn } from "@/lib/utils";
import { Loader } from "lucide-react";

const buttonVariants = cva(
  "inline-flex cursor-pointer items-center justify-center gap-x-2 whitespace-nowrap font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        filled: "",
        outline:
          "border bg-transparent shadow-xs hover:bg-accent  dark:bg-input/30 dark:border-input dark:hover:bg-input/50",
        secondary:
          "bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80",
        ghost: "hover:bg-accent dark:hover:bg-accent/50",
        link: "text-primary underline-offset-4 hover:underline",
        orange: "border border-[#EE9209] text-[#EE9209] ",
        primary: "bg-[#EE9209] text-white",
      },
      colorScheme: {
        primary: "",
        secondary: "",
        destructive: "",
        black: "",
      },
      size: {
        xs: "h-6 rounded px-3 has-[>svg]:px-3 text-xs",
        sm: "h-8 rounded px-4 has-[>svg]:px-3 text-sm",
        md: "h-10 rounded-md px-4 has-[>svg]:px-3 text-sm",
        lg: "h-11 rounded px-6 has-[>svg]:px-4 text-sm",
        xl: "h-12 rounded px-8 has-[>svg]:px-4 text-base",
        "2xl": "h-13 rounded px-9 has-[>svg]:px-9 text-base",
        // icon: "size-9",
      },

      fullWidth: {
        true: "w-full",
        false: "w-max",
      },
      isLoading: {
        true: "opacity-60",
        false: "",
      },
      disabled: {
        true: "opacity-60 cursor-default",
      },
      borderStyle: {
        dashed: "border-dashed",
      },
    },
    compoundVariants: [
      {
        colorScheme: "primary",
        variant: "filled",
        className:
          "bg-primary text-primary-foreground shadow-xs hover:bg-primary/90",
      },
      {
        colorScheme: "primary",
        variant: "outline",
        className: "border-primary text-primary",
      },
      {
        colorScheme: "secondary",
        variant: "filled",
        className: "bg-secondary text-white shadow-xs hover:bg-secondary/90",
      },
      {
        colorScheme: "secondary",
        variant: "outline",
        className: "",
      },
      {
        colorScheme: "destructive",
        variant: "filled",
        className:
          "bg-destructive text-white shadow-xs hover:bg-destructive/90",
      },
      {
        colorScheme: "destructive",
        variant: "outline",
        className: "text-destructive",
      },
      {
        colorScheme: "destructive",
        variant: "ghost",
        className: "text-destructive",
      },
      {
        colorScheme: "black",
        variant: "filled",
        className: "",
      },
      {
        colorScheme: "black",
        variant: "outline",
        className: "border-gray-200",
      },
    ],
    defaultVariants: {
      variant: "filled",
      size: "md",
      colorScheme: "primary",
      // isLoading: ""
    },
  }
);

type ButtonProps = VariantProps<typeof buttonVariants> &
  React.ComponentProps<"button"> & {
    asChild?: boolean;
  };

function Button({
  className,
  variant,
  size,
  asChild = false,
  fullWidth = false,
  isLoading,
  colorScheme,
  borderStyle,
  type = "button",
  ...props
}: ButtonProps) {
  const Comp = asChild ? Slot : "button";
  props.disabled = isLoading || props.disabled;
  if (isLoading) {
    props.children = (
      <div>
        <Loader className="animate-spin" />
      </div>
    );
  }

  props["aria-describedby"] = "";

  return (
    <Comp
      data-slot="button"
      type={type}
      className={cn(
        buttonVariants({
          variant,
          size,
          className,
          fullWidth,
          isLoading,
          disabled: props.disabled,
          colorScheme,
          borderStyle,
        })
      )}
      // aria-describedby=""
      {...props}
    />
  );
}

export { Button, buttonVariants, type ButtonProps };

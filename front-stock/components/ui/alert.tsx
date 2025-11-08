import { cva, type VariantProps } from "class-variance-authority";
import * as React from "react";

import { cn } from "@/lib/utils";

// const alertVariants = cva(
//   "relative w-full rounded border border-gray-300 px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current",
//   {
//     variants: {
//       variant: {
//         default: "bg-card text-card-foreground",
//         destructive:
//           "text-destructive bg-card [&>svg]:text-current *:data-[slot=alert-description]:text-destructive/90",
//       },
//     },
//     defaultVariants: {
//       variant: "default",
//     },
//   }
// );

const alertStyle = cva(
  "relative w-full shrink-0 gap-3 rounded max-w-full h-max break-words whitespace-normal px-4 py-3 [&>svg~*]:pl-9 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-3.5 [&>svg]:top-3",
  {
    variants: {
      variant: {
        solid: "text-white",
        outline: "border",
        soft: "",
        plain: "",
      },
      colorScheme: {
        primary: "",
        red: "",
        green: "",
        black: "",
      },
    },
    compoundVariants: [
      {
        variant: "solid",
        colorScheme: "primary",
        className: "bg-primary-500",
      },
      {
        variant: "outline",
        colorScheme: "primary",
        className: "text-primary-500",
      },
      {
        variant: "soft",
        colorScheme: "primary",
        className: "bg-primary-100 text-primary-500",
      },
      {
        variant: "plain",
        colorScheme: "primary",
        className: "text-primary-500",
      },
      {
        variant: "solid",
        colorScheme: "green",
        className: "bg-green-500",
      },
      {
        variant: "outline",
        colorScheme: "green",
        className: "text-green-600 border-green-300",
      },
      {
        variant: "soft",
        colorScheme: "green",
        className: "bg-green-200 text-green-700",
      },
      {
        variant: "plain",
        colorScheme: "green",
        className: "text-green-500",
      },
      {
        variant: "solid",
        colorScheme: "red",
        className: "bg-red-500",
      },
      {
        variant: "outline",
        colorScheme: "red",
        className: "border-red-300 text-red-600",
      },
      {
        variant: "soft",
        colorScheme: "red",
        className: "bg-red-100 text-red-600",
      },
      {
        variant: "plain",
        colorScheme: "red",
        className: "text-red-600",
      },
      //
      {
        variant: "solid",
        colorScheme: "black",
        className: "bg-gray-900",
      },
      {
        variant: "outline",
        colorScheme: "black",
        className: "text-gray-900",
      },
      {
        variant: "soft",
        colorScheme: "black",
        className: "bg-gray-200 text-gray-900",
      },
      {
        variant: "plain",
        colorScheme: "black",
        className: "text-gray-900",
      },
    ],
    defaultVariants: {
      variant: "solid",
      colorScheme: "black",
    },
  }
);

function Alert({
  className,
  variant,
  colorScheme,
  ...props
}: React.ComponentProps<"div"> & VariantProps<typeof alertStyle>) {
  return (
    <div
      data-slot="alert"
      role="alert"
      className={cn(alertStyle({ variant, colorScheme }), className)}
      {...props}
    />
  );
}

function AlertTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-title"
      className={cn(
        "col-start-2 line-clamp-1 min-h-4 font-medium tracking-tight break-all",
        className
      )}
      {...props}
    />
  );
}

function AlertDescription({
  className,
  ...props
}: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="alert-description"
      className={cn(
        "text-inherit col-start-2 grid justify-items-start gap-1 text-base [&_p]:leading-relaxed break-all",
        className
      )}
      {...props}
    />
  );
}

export { Alert, AlertDescription, AlertTitle };

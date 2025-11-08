import React from "react";

function layout({ children }: { children: React.ReactNode }) {
  return (
    <div>
      {" "}
      <div className="min-h-screen w-full bg-white relative text-gray-800">
        {/* Zigzag Lightning - Light Pattern */}
        <div
          className="absolute inset-0 z-0 pointer-events-none"
          style={{
            backgroundImage: `
        repeating-linear-gradient(0deg, transparent, transparent 20px, rgba(75, 85, 99, 0.08) 20px, rgba(75, 85, 99, 0.08) 21px),
        repeating-linear-gradient(90deg, transparent, transparent 30px, rgba(107, 114, 128, 0.06) 30px, rgba(107, 114, 128, 0.06) 31px),
        repeating-linear-gradient(60deg, transparent, transparent 40px, rgba(55, 65, 81, 0.05) 40px, rgba(55, 65, 81, 0.05) 41px),
        repeating-linear-gradient(150deg, transparent, transparent 35px, rgba(31, 41, 55, 0.04) 35px, rgba(31, 41, 55, 0.04) 36px)
      `,
          }}
        />

        <div className="px-7 flex items-center justify-center relative h-full  min-h-screen">
          <div className="max-w-full -mt-12">
            <div className="flex items-center justify-center mb-10 pr-8">
              App Logo here
            </div>
            <div className="rounded-sm px-7 sm:px-10 pt-7 pb-10 border bg-white shadow-xs">
              {children}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default layout;

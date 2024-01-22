{
    # https://nix.dev/tutorials/first-steps/towards-reproducibility-pinning-nixpkgs#pinning-packages-with-urls-inside-a-nix-expression
    # Picking the commit can be done via https://status.nixos.org/, which lists all the releases and the latest commit that has passed all tests.
    pkgs ? import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/1b64fc1287991a9cce717a01c1973ef86cb1af0b.tar.gz") {}
}: with import <nixpkgs> {};
 
stdenv.mkDerivation {
  name = "symfony";
 
  buildInputs = [
    vim
    which
    coreutils
    php83
    php83Packages.composer
    git
    jq
    roadrunner
    gh
    gnupg
    httpie
    just
    symfony-cli
    nodePackages.pnpm
    stripe-cli
    tailwindcss
  ];
  
  GIT_EDITOR = "${pkgs.vim}/bin/vi";
  
  shellHook = ''
    git --version
    php -v
    composer --version
    symfony -V
    rr -v
    echo "pnpm version: " && pnpm -v
  '';
}
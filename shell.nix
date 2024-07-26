{
    # Pinning packages with URLs inside a Nix expression
    # https://nix.dev/tutorials/first-steps/towards-reproducibility-pinning-nixpkgs#pinning-packages-with-urls-inside-a-nix-expression
    # Picking the commit can be done via https://status.nixos.org,
    # which lists all the releases and the latest commit that has passed all tests.
    pkgs ? import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/2122a9b35b35719ad9a395fe783eabb092df01b1.tar.gz") {},
}:

pkgs.mkShell {
    buildInputs = [
        pkgs.vim
        pkgs.coreutils
        pkgs.git
        pkgs.php83
        pkgs.just
    ];

    shellHook = ''
        git --version
    '';
}


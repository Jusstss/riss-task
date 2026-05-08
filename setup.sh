#!/bin/bash

# setup.sh - Install PHP CLI and run PHP scripts from the command line

set -e

echo "=== PHP CLI Setup ==="

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
else
    OS=$(uname -s)
fi

install_php_linux() {
    if command -v apt-get &>/dev/null; then
        echo "[*] Updating package list..."
        sudo apt-get update -y

        echo "[*] Installing PHP CLI..."
        sudo apt-get install -y php-cli

    elif command -v yum &>/dev/null; then
        echo "[*] Installing PHP CLI via yum..."
        sudo yum install -y php-cli

    elif command -v dnf &>/dev/null; then
        echo "[*] Installing PHP CLI via dnf..."
        sudo dnf install -y php-cli

    else
        echo "[!] Unsupported Linux package manager. Please install PHP manually."
        exit 1
    fi
}

install_php_mac() {
    if command -v brew &>/dev/null; then
        echo "[*] Installing PHP via Homebrew..."
        brew install php
    else
        echo "[!] Homebrew not found. Install it from https://brew.sh then re-run this script."
        exit 1
    fi
}

# Check if PHP is already installed
if command -v php &>/dev/null; then
    echo "[✓] PHP is already installed: $(php --version | head -n 1)"
else
    echo "[*] PHP not found. Installing..."

    case "$OS" in
        ubuntu|debian|linuxmint|pop)
            install_php_linux
            ;;
        centos|rhel|fedora|almalinux|rocky)
            install_php_linux
            ;;
        darwin)
            install_php_mac
            ;;
        *)
            # Fallback detection
            if [[ "$OSTYPE" == "darwin"* ]]; then
                install_php_mac
            else
                install_php_linux
            fi
            ;;
    esac

    echo "[✓] PHP installed: $(php --version | head -n 1)"
fi
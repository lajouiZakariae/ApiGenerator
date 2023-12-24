<?php

namespace Zakalajo\ApiGenerator\Interfaces;

interface IGenerator {
    function loadData(): void;

    function ensureFolderExists(): void;

    function fileExists(): bool;

    function generateFile(): void;
}

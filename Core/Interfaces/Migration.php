<?
namespace Core\Interfaces;

interface Migration {
    public function migrate();
    public function rollback();
}